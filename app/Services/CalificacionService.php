<?php

namespace App\Services;

use App\Exceptions\CalificacionException;
use App\Models\Alumno;
use App\Models\Inscripcion;
use App\Models\Periodo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CalificacionService
{
    const CALIFICACION_MIN = 0;
    const CALIFICACION_MAX = 100;

    /**
     * Cache por-request de las materias para capturar.
     * Evita ejecutar la misma query múltiples veces en un solo render.
     */
    private ?array $cacheMaterias = null;
    private ?int $cacheAlumnoId = null;

    /**
     * Guarda las calificaciones (3 parciales) de una inscripción.
     */
    public function guardar(Inscripcion $inscripcion, Alumno $alumno, array $parciales): Inscripcion
    {
        if ($inscripcion->alumno_id !== $alumno->id) {
            throw CalificacionException::noEsTuInscripcion();
        }

        if ($inscripcion->estatus === 'dada_de_baja') {
            throw CalificacionException::inscripcionBaja();
        }

        $periodo = $inscripcion->periodo;
        if (!$periodo || now()->gt($periodo->fecha_fin)) {
            throw CalificacionException::periodoNoEditable();
        }

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

        // Invalidar cache del servicio antes de la operación
        $this->cacheMaterias = null;

        return DB::transaction(function () use ($inscripcion, $parciales) {
            $inscripcion->update([
                'parcial1' => $this->normalizar($parciales['parcial1'] ?? null),
                'parcial2' => $this->normalizar($parciales['parcial2'] ?? null),
                'parcial3' => $this->normalizar($parciales['parcial3'] ?? null),
            ]);

            return $inscripcion->fresh();
        });
    }

    /**
     * Obtiene las inscripciones del periodo actual con info completa.
     * Cacheado en memoria durante el request.
     */
    public function obtenerMateriasParaCapturar(Alumno $alumno): Collection
    {
        if ($this->cacheMaterias !== null && $this->cacheAlumnoId === $alumno->id) {
            return collect($this->cacheMaterias);
        }

        $periodo = Periodo::where('es_actual', true)->first();
        if (!$periodo) {
            $this->cacheMaterias = [];
            $this->cacheAlumnoId = $alumno->id;
            return collect();
        }

        $materias = $alumno->inscripciones()
            ->where('periodo_id', $periodo->id)
            ->whereIn('estatus', ['en_curso', 'aprobada', 'reprobada'])
            ->with('materiaMalla:id,clave,nombre,creditos,semestre')
            ->get()
            ->map(fn($insc) => [
                'id'        => $insc->id,
                'clave'     => $insc->materiaMalla->clave,
                'nombre'    => $insc->materiaMalla->nombre,
                'creditos'  => $insc->materiaMalla->creditos,
                'semestre'  => $insc->materiaMalla->semestre,
                'parcial1'  => $insc->parcial1,
                'parcial2'  => $insc->parcial2,
                'parcial3'  => $insc->parcial3,
                'promedio'  => $insc->promedio,
                'estatus'   => $insc->estatus,
                'parciales_capturados' => ($insc->parcial1 !== null ? 1 : 0)
                    + ($insc->parcial2 !== null ? 1 : 0)
                    + ($insc->parcial3 !== null ? 1 : 0),
            ])
            ->values()
            ->all();

        $this->cacheMaterias = $materias;
        $this->cacheAlumnoId = $alumno->id;

        return collect($materias);
    }

    /**
     * Estadísticas del periodo actual para el header.
     */
    public function obtenerResumenPeriodo(Alumno $alumno): array
    {
        $materias = $this->obtenerMateriasParaCapturar($alumno);

        $finalizadas = $materias->whereIn('estatus', ['aprobada', 'reprobada']);
        $aprobadas   = $materias->where('estatus', 'aprobada');
        $reprobadas  = $materias->where('estatus', 'reprobada');
        $pendientes  = $materias->where('estatus', 'en_curso');

        $promedioPeriodo = $finalizadas->isEmpty()
            ? 0
            : round($finalizadas->avg('promedio'), 2);

        return [
            'total_materias'    => $materias->count(),
            'calificadas'       => $finalizadas->count(),
            'pendientes'        => $pendientes->count(),
            'aprobadas'         => $aprobadas->count(),
            'reprobadas'        => $reprobadas->count(),
            'promedio_periodo'  => $promedioPeriodo,
            'promedio_general'  => (float) ($alumno->promedio_general ?? 0),
        ];
    }

    private function normalizar($valor): ?float
    {
        if ($valor === null || $valor === '') return null;
        return round((float) $valor, 2);
    }
}