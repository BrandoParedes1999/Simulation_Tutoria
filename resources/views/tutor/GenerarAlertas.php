<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Alumno;
use App\Models\Alerta;
use App\Models\ReglasAlerta;
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

        $periodoActual = Periodo::where('es_actual', '!=', 0)
            ->orderByDesc('fecha_inicio')
            ->first();

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
        Alumno       $alumno,
        ReglaAlerta $regla,
        ?Periodo     $periodo,
        int          &$creadas
    ): void {
        switch ($regla->clave_regla) {

            // Calificación en materia por debajo del umbral
            // Umbral en BD: 70.00 → equivale a 7.0 en escala 0-10
            case 'calificacion_minima_materia':
                if (!$periodo) break;

                $umbral = $regla->umbral >= 10
                    ? $regla->umbral / 10
                    : $regla->umbral;

                $inscripcionesBajas = Inscripcion::where('alumno_id', $alumno->id)
                    ->where('periodo_id', $periodo->id)
                    ->whereNotNull('calificacion_final')
                    ->where('calificacion_final', '>', 0)
                    ->where('calificacion_final', '<', $umbral)
                    ->with('materiaMalla')
                    ->get();

                foreach ($inscripcionesBajas as $insc) {
                    $nombreMateria = $insc->materiaMalla->nombre ?? 'materia';
                    $this->crearSiNoExiste(
                        alumnoId:      $alumno->id,
                        prioridad:     $regla->prioridad_alerta,
                        categoria:     'calificacion',
                        titulo:        "Calificación baja en {$nombreMateria}",
                        mensaje:       "Calificación {$insc->calificacion_final} por debajo del mínimo ({$umbral}).",
                        inscripcionId: $insc->id,
                        creadas:       $creadas
                    );
                }
                break;

            // Promedio semestral por debajo del umbral
            // Umbral en BD: 80.00 → equivale a 8.0 en escala 0-10
            case 'promedio_semestral_minimo':
                $umbral = $regla->umbral >= 10
                    ? $regla->umbral / 10
                    : $regla->umbral;

                $promedio = (float)$alumno->promedio_general;

                if ($promedio > 0 && $promedio < $umbral) {
                    $this->crearSiNoExiste(
                        alumnoId:  $alumno->id,
                        prioridad: $regla->prioridad_alerta,
                        categoria: 'promedio',
                        titulo:    'Promedio semestral ' . number_format($promedio, 1),
                        mensaje:   "Promedio de {$promedio} por debajo del mínimo ({$umbral}).",
                        creadas:   $creadas
                    );
                }
                break;

            // Caída de calificación entre parciales
            // Umbral en BD: 10.00 → equivale a 1.0 punto en escala 0-10
            case 'caida_calificacion_puntos':
                if (!$periodo) break;

                $umbralCaida = $regla->umbral >= 10
                    ? $regla->umbral / 10
                    : $regla->umbral;

                $inscripciones = Inscripcion::where('alumno_id', $alumno->id)
                    ->where('periodo_id', $periodo->id)
                    ->whereNotNull('parcial1')
                    ->whereNotNull('parcial2')
                    ->with('materiaMalla')
                    ->get();

                foreach ($inscripciones as $insc) {
                    $caida = (float)$insc->parcial1 - (float)$insc->parcial2;
                    if ($caida >= $umbralCaida) {
                        $nombreMateria = $insc->materiaMalla->nombre ?? 'materia';
                        $this->crearSiNoExiste(
                            alumnoId:      $alumno->id,
                            prioridad:     $regla->prioridad_alerta,
                            categoria:     'caida_calificacion',
                            titulo:        "Caída de " . number_format($caida, 1) . " pts en {$nombreMateria}",
                            mensaje:       "Caída de {$caida} puntos entre parcial 1 y parcial 2.",
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
        // No crea duplicados: si ya existe una alerta pendiente con el mismo título la omite
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