<?php

namespace App\Observers;

use App\Models\Inscripcion;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class InscripcionObserver
{
    public function created(Inscripcion $inscripcion): void
    {
        $this->limpiarCacheMalla($inscripcion);
    }

    public function updated(Inscripcion $inscripcion): void
    {
        $this->limpiarCacheMalla($inscripcion);

        if ($inscripcion->wasChanged('estatus')) {
            $this->actualizarTotalesAlumno($inscripcion);
        }
    }

    public function deleted(Inscripcion $inscripcion): void
    {
        $this->limpiarCacheMalla($inscripcion);
        $this->actualizarTotalesAlumno($inscripcion);
    }

    private function limpiarCacheMalla(Inscripcion $inscripcion): void
    {
        $alumno = $inscripcion->alumno;
        if ($alumno) {
            Cache::forget("malla_carrera_{$alumno->carrera_id}");
        }
    }

    /**
     * Actualiza créditos aprobados y promedio general con UNA sola query agregada.
     * Mucho más rápido que calcularCreditosAprobados() + calcularPromedioGeneral()
     * que hacían queries separadas y cargaban colecciones completas.
     */
    private function actualizarTotalesAlumno(Inscripcion $inscripcion): void
    {
        $alumno = $inscripcion->alumno;
        if (!$alumno) return;

        $stats = DB::table('inscripciones')
            ->join('materias_malla', 'inscripciones.materia_malla_id', '=', 'materias_malla.id')
            ->where('inscripciones.alumno_id', $alumno->id)
            ->where('inscripciones.estatus', 'aprobada')
            ->selectRaw('COALESCE(SUM(materias_malla.creditos), 0) as total_creditos')
            ->selectRaw('COALESCE(AVG(CASE WHEN inscripciones.promedio IS NOT NULL THEN inscripciones.promedio END), 0) as promedio_general')
            ->first();

        $alumno->updateQuietly([
            'creditos_aprobados' => (int) $stats->total_creditos,
            'promedio_general'   => round((float) $stats->promedio_general, 2),
        ]);
    }
}