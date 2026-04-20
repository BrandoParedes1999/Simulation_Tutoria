<?php

namespace App\Observers;

use App\Models\Inscripcion;
use Illuminate\Support\Facades\Cache;

class InscripcionObserver
{
    /**
     * Se ejecuta después de crear, actualizar o borrar una inscripción.
     * Mantiene sincronizados: cache de malla, créditos aprobados y promedio general del alumno.
     */

    public function created(Inscripcion $inscripcion): void
    {
        $this->limpiarCacheMalla($inscripcion);
    }

    public function updated(Inscripcion $inscripcion): void
    {
        $this->limpiarCacheMalla($inscripcion);

        // Si cambió el estatus a aprobada/reprobada, actualizar totales del alumno
        if ($inscripcion->wasChanged('estatus')) {
            $this->actualizarTotalesAlumno($inscripcion);
        }
    }

    public function deleted(Inscripcion $inscripcion): void
    {
        $this->limpiarCacheMalla($inscripcion);
        $this->actualizarTotalesAlumno($inscripcion);
    }

    // ─── Privados ──────────────────────────────────────────

    private function limpiarCacheMalla(Inscripcion $inscripcion): void
    {
        $alumno = $inscripcion->alumno;
        if ($alumno) {
            Cache::forget("malla_carrera_{$alumno->carrera_id}");
        }
    }

    private function actualizarTotalesAlumno(Inscripcion $inscripcion): void
    {
        $alumno = $inscripcion->alumno;
        if (!$alumno) return;

        $alumno->update([
            'creditos_aprobados' => $alumno->calcularCreditosAprobados(),
            'promedio_general' => $alumno->calcularPromedioGeneral(),
        ]);
    }
}