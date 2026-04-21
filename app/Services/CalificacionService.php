<?php

namespace App\Services;

use App\Exceptions\CalificacionException;
use App\Models\Alumno;
use App\Models\Inscripcion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CalificacionService
{
    const CALIFICACION_MIN = 0;
    const CALIFICACION_MAX = 100;

    /**
     * Guarda las calificaciones (3 parciales) de una inscripción.
     *
     * @param  array  $parciales  ['parcial1' => ?float, 'parcial2' => ?float, 'parcial3' => ?float]
     */
    public function guardar(Inscripcion $inscripcion, Alumno $alumno, array $parciales): Inscripcion
    {
        // Seguridad: la inscripción debe ser del alumno
        if ($inscripcion->alumno_id !== $alumno->id) {
            throw CalificacionException::noEsTuInscripcion();
        }

        // No se puede capturar en una materia dada de baja
        if ($inscripcion->estatus === 'dada_de_baja') {
            throw CalificacionException::inscripcionBaja();
        }

        // Verificar que el periodo esté abierto
        $periodo = $inscripcion->periodo;
        if (!$periodo || now()->gt($periodo->fecha_fin)) {
            throw CalificacionException::periodoNoEditable();
        }

        // Validar rangos de cada parcial
        foreach (['parcial1', 'parcial2', 'parcial3'] as $campo) {
            $valor = $parciales[$campo] ?? null;
            if ($valor !== null && $valor !== '') {
                $valor = (float) $valor;
                if ($valor < self::CALIFICACION_MIN || $valor > self::CALIFICACION_MAX) {
                    throw CalificacionException::fueraDeRango(
                        str_replace('parcial', 'Parcial ', $campo),
                        $valor
                    );
                }
            }
        }

        return DB::transaction(function () use ($inscripcion, $parciales) {
            $inscripcion->update([
                'parcial1' => $this->normalizar($parciales['parcial1'] ?? null),
                'parcial2' => $this->normalizar($parciales['parcial2'] ?? null),
                'parcial3' => $this->normalizar($parciales['parcial3'] ?? null),
            ]);

            // El Observer se encarga del resto (créditos, promedio general, cache)
            return $inscripcion->fresh();
        });
    }

    /**
     * Obtiene las inscripciones del periodo actual con info completa para la vista.
     */
    public function obtenerMateriasParaCapturar(Alumno $alumno): Collection
    {
        $periodo = \App\Models\Periodo::where('es_actual', true)->first();
        if (!$periodo) return collect();

        return $alumno->inscripciones()
            ->where('periodo_id', $periodo->id)
            ->whereIn('estatus', ['en_curso', 'aprobada', 'reprobada'])
            ->with('materiaMalla:id,clave,nombre,creditos,semestre')
            ->get()
            ->map(fn($insc) => [
                'id' => $insc->id,
                'clave' => $insc->materiaMalla->clave,
                'nombre' => $insc->materiaMalla->nombre,
                'creditos' => $insc->materiaMalla->creditos,
                'semestre' => $insc->materiaMalla->semestre,
                'parcial1' => $insc->parcial1,
                'parcial2' => $insc->parcial2,
                'parcial3' => $insc->parcial3,
                'promedio' => $insc->promedio,
                'estatus' => $insc->estatus,
                'parciales_capturados' => collect([$insc->parcial1, $insc->parcial2, $insc->parcial3])
                    ->filter(fn($v) => $v !== null)->count(),
            ])
            ->values();
    }

    /**
     * Estadísticas del periodo actual para el header.
     */
    public function obtenerResumenPeriodo(Alumno $alumno): array
    {
        $materias = $this->obtenerMateriasParaCapturar($alumno);

        $conCalificacionCompleta = $materias->filter(fn($m) => $m['parciales_capturados'] === 3);
        $aprobadas = $materias->where('estatus', 'aprobada');
        $reprobadas = $materias->where('estatus', 'reprobada');

        $promedioPeriodo = $conCalificacionCompleta->isEmpty()
            ? 0
            : round($conCalificacionCompleta->avg('promedio'), 2);

        return [
            'total_materias' => $materias->count(),
            'calificadas' => $conCalificacionCompleta->count(),
            'pendientes' => $materias->count() - $conCalificacionCompleta->count(),
            'aprobadas' => $aprobadas->count(),
            'reprobadas' => $reprobadas->count(),
            'promedio_periodo' => $promedioPeriodo,
            'promedio_general' => $alumno->promedio_general ?? 0,
        ];
    }

    // ─── Helpers ──────────────────────────

    private function normalizar($valor): ?float
    {
        if ($valor === null || $valor === '') return null;
        return round((float) $valor, 2);
    }
}