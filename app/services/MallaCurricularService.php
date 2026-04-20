<?php

namespace App\Services;

use App\Models\Alumno;
use App\Models\MateriaMalla;
use App\Models\Inscripcion;
use Illuminate\Support\Facades\Cache;

class MallaCurricularService
{
    const ESTADO_APROBADA = 'aprobada';
    const ESTADO_EN_CURSO = 'en_curso';
    const ESTADO_DISPONIBLE = 'disponible';
    const ESTADO_REPROBADA = 'reprobada';
    const ESTADO_BLOQUEADA = 'bloqueada';

    /**
     * Retorna la malla completa del alumno con el estado de cada materia.
     * Usa eager loading para evitar N+1 y cache corto para la malla base.
     */
    public function obtenerMallaDelAlumno(Alumno $alumno): array
    {
        // Cacheamos las materias + prerrequisitos por 10 minutos
        // Son datos que casi nunca cambian
        $materias = Cache::remember(
            "malla_carrera_{$alumno->carrera_id}",
            600,
            fn() => MateriaMalla::where('carrera_id', $alumno->carrera_id)
                ->where('activa', true)
                ->with('prerrequisitos:id,clave,nombre')
                ->orderBy('semestre')
                ->orderBy('clave')
                ->get()
        );

        // Cargamos las inscripciones del alumno una sola vez
        $inscripciones = Inscripcion::where('alumno_id', $alumno->id)
            ->select('id', 'materia_malla_id', 'estatus', 'promedio')
            ->get()
            ->keyBy('materia_malla_id');

        // Pre-calculamos los IDs por estado (O(n) en vez de O(n²))
        $aprobadasIds = [];
        $enCursoIds = [];
        $reprobadasIds = [];

        foreach ($inscripciones as $insc) {
            match($insc->estatus) {
                'aprobada' => $aprobadasIds[$insc->materia_malla_id] = true,
                'en_curso' => $enCursoIds[$insc->materia_malla_id] = true,
                'reprobada' => $reprobadasIds[$insc->materia_malla_id] = true,
                default => null,
            };
        }

        // Agrupar por semestre
        $porSemestre = [];

        foreach ($materias as $materia) {
            $estado = $this->calcularEstado(
                $materia,
                $aprobadasIds,
                $enCursoIds,
                $reprobadasIds
            );

            $porSemestre[$materia->semestre][] = [
                'id' => $materia->id,
                'clave' => $materia->clave,
                'nombre' => $materia->nombre,
                'creditos' => $materia->creditos,
                'total_horas' => $materia->total_horas,
                'tipo' => $materia->tipo,
                'area' => $materia->area,
                'nivel' => $materia->nivel,
                'estado' => $estado,
                'calificacion' => $inscripciones[$materia->id]->promedio ?? null,
                'prerrequisitos' => $materia->prerrequisitos->map(fn($p) => [
                    'clave' => $p->clave,
                    'nombre' => $p->nombre,
                    'cumplido' => isset($aprobadasIds[$p->id]),
                ])->toArray(),
            ];
        }

        return $porSemestre;
    }

    /**
     * Calcula el estado de una materia usando arrays asociativos
     * (mucho más rápido que in_array).
     */
    private function calcularEstado(
        MateriaMalla $materia,
        array $aprobadasIds,
        array $enCursoIds,
        array $reprobadasIds
    ): string {
        if (isset($aprobadasIds[$materia->id])) {
            return self::ESTADO_APROBADA;
        }

        if (isset($enCursoIds[$materia->id])) {
            return self::ESTADO_EN_CURSO;
        }

        if (isset($reprobadasIds[$materia->id])) {
            return $this->prerrequisitosCumplidos($materia, $aprobadasIds)
                ? self::ESTADO_REPROBADA
                : self::ESTADO_BLOQUEADA;
        }

        if ($materia->prerrequisitos->isEmpty()) {
            return self::ESTADO_DISPONIBLE;
        }

        return $this->prerrequisitosCumplidos($materia, $aprobadasIds)
            ? self::ESTADO_DISPONIBLE
            : self::ESTADO_BLOQUEADA;
    }

    private function prerrequisitosCumplidos(
        MateriaMalla $materia,
        array $aprobadasIds
    ): bool {
        foreach ($materia->prerrequisitos as $prereq) {
            if (!isset($aprobadasIds[$prereq->id])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Estadísticas del alumno con queries optimizadas en una sola pasada.
     */
    public function obtenerEstadisticas(Alumno $alumno): array
    {
        // Total de créditos de la carrera (cacheado, nunca cambia)
        $totalCreditos = Cache::remember(
            "total_creditos_carrera_{$alumno->carrera_id}",
            3600,
            fn() => (int) MateriaMalla::where('carrera_id', $alumno->carrera_id)
                ->where('activa', true)
                ->sum('creditos')
        );

        $totalMaterias = Cache::remember(
            "total_materias_carrera_{$alumno->carrera_id}",
            3600,
            fn() => MateriaMalla::where('carrera_id', $alumno->carrera_id)
                ->where('activa', true)
                ->count()
        );

        // Una sola query para todas las inscripciones con su estatus y créditos
        $inscripciones = Inscripcion::where('alumno_id', $alumno->id)
            ->join('materias_malla', 'inscripciones.materia_malla_id', '=', 'materias_malla.id')
            ->select('inscripciones.estatus', 'materias_malla.creditos')
            ->get();

        $creditosAprobados = 0;
        $creditosEnCurso = 0;
        $materiasAprobadas = 0;

        foreach ($inscripciones as $insc) {
            if ($insc->estatus === 'aprobada') {
                $creditosAprobados += $insc->creditos;
                $materiasAprobadas++;
            } elseif ($insc->estatus === 'en_curso') {
                $creditosEnCurso += $insc->creditos;
            }
        }

        $progreso = $totalCreditos > 0
            ? round(($creditosAprobados / $totalCreditos) * 100, 1)
            : 0;

        return [
            'total_creditos' => $totalCreditos,
            'creditos_aprobados' => $creditosAprobados,
            'creditos_en_curso' => $creditosEnCurso,
            'creditos_restantes' => $totalCreditos - $creditosAprobados - $creditosEnCurso,
            'total_materias' => $totalMaterias,
            'materias_aprobadas' => $materiasAprobadas,
            'porcentaje_avance' => $progreso,
        ];
    }

    /**
     * Limpia el cache cuando hay cambios.
     * Se llama desde InscripcionService cuando se inscribe o aprueba una materia.
     */
    public function limpiarCache(Alumno $alumno): void
    {
        Cache::forget("malla_carrera_{$alumno->carrera_id}");
    }
}