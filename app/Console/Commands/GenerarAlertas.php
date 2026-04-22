<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Alumno;
use App\Models\Alerta;
use App\Models\Inscripcion;
use App\Models\Periodo;
use App\Models\ReglaAlerta;

class GenerarAlertas extends Command
{
    protected $signature   = 'alertas:generar';
    protected $description = 'Genera alertas automáticamente según las reglas del tutor';

    public function handle(): void
    {
        $this->info('Revisando reglas de alerta...');

        $alumnos = Alumno::where('estatus', 'activo')->get();
        $creadas = 0;

        // FIXED: antes usaba 'es_actual != 0', ahora usa el campo booleano correcto
        $periodoActual = Periodo::where('es_actual', true)->first();

        foreach ($alumnos as $alumno) {
            if (!$alumno->tutor_id) continue;

            $reglas = ReglaAlerta::where('tutor_id', $alumno->tutor_id)
                ->where('activa', true)
                ->get();

            foreach ($reglas as $regla) {
                $this->procesarRegla($alumno, $regla, $periodoActual, $creadas);
            }
        }

        $this->info("Proceso terminado. Alertas nuevas creadas: {$creadas}");
    }

    private function procesarRegla(
        Alumno      $alumno,
        ReglaAlerta $regla,
        ?Periodo    $periodo,
        int         &$creadas
    ): void {
        /*
         * FIXED (bug crítico de escala):
         * Antes: dividía el umbral entre 10 cuando >= 10 (confundía escala 0-10 con 0-100).
         * El seeder crea umbrales en escala 0-100 (70, 80, 10).
         * Toda la app trabaja en escala 0-100.  → usar umbral directamente.
         */
        $umbral = $regla->umbral;

        switch ($regla->clave_regla) {

            // ── Calificación parcial por debajo del umbral ──
            case 'calificacion_minima_materia':
                if (!$periodo) break;

                $inscripcionesBajas = Inscripcion::where('alumno_id', $alumno->id)
                    ->where('periodo_id', $periodo->id)
                    ->whereNotNull('promedio')
                    ->where('promedio', '>', 0)
                    ->where('promedio', '<', $umbral)     // ej. < 70 en escala 0-100
                    ->with('materiaMalla:id,nombre')
                    ->get();

                foreach ($inscripcionesBajas as $insc) {
                    $nombreMateria = $insc->materiaMalla->nombre ?? 'materia desconocida';
                    $this->crearSiNoExiste(
                        alumnoId:      $alumno->id,
                        prioridad:     $regla->prioridad_alerta,
                        categoria:     'calificacion_baja',
                        titulo:        "Calificación baja en {$nombreMateria}",
                        mensaje:       "Promedio parcial de {$insc->promedio} por debajo del mínimo ({$umbral} pts).",
                        inscripcionId: $insc->id,
                        creadas:       $creadas
                    );
                }
                break;

            // ── Promedio general por debajo del umbral ──
            case 'promedio_semestral_minimo':
                $promedio = (float) $alumno->promedio_general;

                if ($promedio > 0 && $promedio < $umbral) {  // ej. < 80 en escala 0-100
                    $this->crearSiNoExiste(
                        alumnoId:  $alumno->id,
                        prioridad: $regla->prioridad_alerta,
                        categoria: 'promedio_semestral_bajo',
                        titulo:    'Promedio general bajo: ' . number_format($promedio, 1),
                        mensaje:   "Promedio de {$promedio} por debajo del mínimo requerido ({$umbral} pts).",
                        creadas:   $creadas
                    );
                }
                break;

            // ── Caída de calificación entre parciales ──
            case 'caida_calificacion_puntos':
                if (!$periodo) break;

                $inscripciones = Inscripcion::where('alumno_id', $alumno->id)
                    ->where('periodo_id', $periodo->id)
                    ->whereNotNull('parcial1')
                    ->whereNotNull('parcial2')
                    ->with('materiaMalla:id,nombre')
                    ->get();

                foreach ($inscripciones as $insc) {
                    $caida = (float) $insc->parcial1 - (float) $insc->parcial2;
                    if ($caida >= $umbral) {  // ej. >= 10 puntos de caída (0-100)
                        $nombreMateria = $insc->materiaMalla->nombre ?? 'materia desconocida';
                        $this->crearSiNoExiste(
                            alumnoId:      $alumno->id,
                            prioridad:     $regla->prioridad_alerta,
                            categoria:     'caida_calificacion',
                            titulo:        "Caída de " . number_format($caida, 1) . " pts en {$nombreMateria}",
                            mensaje:       "Caída de {$caida} puntos entre P1 ({$insc->parcial1}) y P2 ({$insc->parcial2}).",
                            inscripcionId: $insc->id,
                            creadas:       $creadas
                        );
                    }
                }
                break;
        }
    }

    private function crearSiNoExiste(
        int    $alumnoId,
        string $prioridad,
        string $categoria,
        string $titulo,
        string $mensaje,
        ?int   $materiaId     = null,
        ?int   $inscripcionId = null,
        int    &$creadas
    ): void {
        $existe = Alerta::where('alumno_id', $alumnoId)
            ->where('titulo', $titulo)
            ->where('atendida', false)
            ->exists();

        if (!$existe) {
            Alerta::create([
                'alumno_id'                  => $alumnoId,
                'prioridad'                  => $prioridad,
                'categoria'                  => $categoria,
                'titulo'                     => $titulo,
                'mensaje'                    => $mensaje,
                'materia_relacionada_id'     => $materiaId,
                'inscripcion_relacionada_id' => $inscripcionId,
                'atendida'                   => false,
            ]);
            $creadas++;
        }
    }
}