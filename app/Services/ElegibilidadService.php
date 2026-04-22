<?php

namespace App\Services;

use App\Models\Alumno;
use App\Models\Inscripcion;
use App\Models\Mensaje;
use App\Models\Periodo;
use Illuminate\Support\Facades\DB;

class ElegibilidadService
{
    const SS_HORAS = 480;
    const SS_PORCENTAJE_CREDITOS = 70;
    const SS_SEMESTRE_MIN = 5;

    const PP_HORAS = 320;
    const PP_PORCENTAJE_CREDITOS = 60;
    const PP_SEMESTRE_MIN = 6;

    /**
     * Evalúa elegibilidad para SS y PP.
     */
    public function evaluar(Alumno $alumno): array
    {
        $totalCreditos = (int) DB::table('materias_malla')
            ->where('carrera_id', $alumno->carrera_id)
            ->where('activa', true)
            ->whereNotIn('tipo', ['especial'])
            ->sum('creditos');

        $creditosAprobados = (int) ($alumno->creditos_aprobados ?? 0);
        $porcentaje = $totalCreditos > 0 ? round(($creditosAprobados / $totalCreditos) * 100, 1) : 0;
        $semestreActual = (int) $alumno->semestre_actual;

        return [
            'servicio_social' => $this->evaluarRequisito(
                $creditosAprobados, $totalCreditos, $porcentaje, $semestreActual,
                self::SS_PORCENTAJE_CREDITOS, self::SS_SEMESTRE_MIN, self::SS_HORAS, 'Servicio Social'
            ),
            'practicas_profesionales' => $this->evaluarRequisito(
                $creditosAprobados, $totalCreditos, $porcentaje, $semestreActual,
                self::PP_PORCENTAJE_CREDITOS, self::PP_SEMESTRE_MIN, self::PP_HORAS, 'Prácticas Profesionales'
            ),
            'porcentaje_avance'  => $porcentaje,
            'creditos_aprobados' => $creditosAprobados,
            'total_creditos'     => $totalCreditos,
        ];
    }

    private function evaluarRequisito(
        int $creditosAprobados, int $totalCreditos, float $porcentajeActual,
        int $semestreActual, int $porcentajeRequerido, int $semestreMinimo,
        int $horas, string $nombre
    ): array {
        $creditosRequeridos = (int) round($totalCreditos * ($porcentajeRequerido / 100));
        $cumpleCreditos = $creditosAprobados >= $creditosRequeridos;
        $cumpleSemestre = $semestreActual >= $semestreMinimo;
        $elegible = $cumpleCreditos && $cumpleSemestre;

        $progresoCreditos = $creditosRequeridos > 0
            ? min(100, round(($creditosAprobados / $creditosRequeridos) * 100, 1))
            : 0;

        $semestresFaltantes = $cumpleSemestre ? 0 : $semestreMinimo - $semestreActual;

        return [
            'nombre'               => $nombre,
            'horas'                => $horas,
            'elegible'             => $elegible,
            'cumple_creditos'      => $cumpleCreditos,
            'cumple_semestre'      => $cumpleSemestre,
            'porcentaje_requerido' => $porcentajeRequerido,
            'porcentaje_actual'    => $porcentajeActual,
            'semestre_requerido'   => $semestreMinimo,
            'semestre_actual'      => $semestreActual,
            'creditos_aprobados'   => $creditosAprobados,
            'creditos_requeridos'  => $creditosRequeridos,
            'progreso_creditos'    => $progresoCreditos,
            'semestres_faltantes'  => $semestresFaltantes,
        ];
    }

    /**
     * Evolución del promedio por periodo (histórico).
     */
    public function obtenerEvolucionPromedio(Alumno $alumno): array
    {
        return DB::table('inscripciones')
            ->join('periodos', 'inscripciones.periodo_id', '=', 'periodos.id')
            ->where('inscripciones.alumno_id', $alumno->id)
            ->whereIn('inscripciones.estatus', ['aprobada', 'reprobada'])
            ->whereNotNull('inscripciones.promedio')
            ->groupBy('periodos.id', 'periodos.clave', 'periodos.fecha_inicio')
            ->orderBy('periodos.fecha_inicio')
            ->select(
                'periodos.clave',
                DB::raw('ROUND(AVG(inscripciones.promedio), 2) as promedio')
            )
            ->get()
            ->map(fn($r) => [
                'clave'    => $r->clave,
                'promedio' => (float) $r->promedio,
            ])
            ->toArray();
    }

    /**
     * Datos del periodo actual: KPIs + materias para el radar.
     */
    public function obtenerDatosPeriodo(Alumno $alumno, ?Periodo $periodo): array
    {
        if (!$periodo) {
            return [
                'promedio_semestral' => 0,
                'materias_en_curso'  => 0,
                'creditos_periodo'   => 0,
                'materias_radar'     => [],
            ];
        }

        $inscripciones = Inscripcion::where('alumno_id', $alumno->id)
            ->where('periodo_id', $periodo->id)
            ->whereIn('estatus', ['en_curso', 'aprobada', 'reprobada'])
            ->with('materiaMalla:id,clave,nombre,creditos')
            ->get();

        $radar = [];
        $promedios = [];

        foreach ($inscripciones as $i) {
            $parciales = array_filter(
                [$i->parcial1, $i->parcial2, $i->parcial3],
                fn($p) => $p !== null
            );

            if (count($parciales) === 0) continue;

            $promedio = round(array_sum($parciales) / count($parciales), 1);
            $promedios[] = $promedio;

            $radar[] = [
                'clave'    => $i->materiaMalla->clave,
                'nombre'   => $i->materiaMalla->nombre,
                'promedio' => $promedio,
                'estatus'  => $i->estatus,
            ];
        }

        $promedioSemestral = count($promedios) > 0
            ? round(array_sum($promedios) / count($promedios), 1)
            : 0;

        return [
            'promedio_semestral' => $promedioSemestral,
            'materias_en_curso'  => $inscripciones->where('estatus', 'en_curso')->count(),
            'creditos_periodo'   => (int) $inscripciones->sum(fn($i) => $i->materiaMalla->creditos),
            'materias_radar'     => $radar,
        ];
    }

    /**
     * Últimos mensajes recibidos por el alumno.
     */
    public function obtenerMensajesRecientes(Alumno $alumno, int $limit = 3)
    {
        return Mensaje::where('destinatario_id', $alumno->usuario_id)
            ->with('remitente:id,name')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn($m) => [
                'id'         => $m->id,
                'asunto'     => $m->asunto,
                'remitente'  => $m->remitente->name ?? 'Sistema',
                'fecha'      => $m->created_at->locale('es')->isoFormat('D MMM'),
                'leido'      => $m->leido_en !== null,
                'prioridad'  => $m->prioridad,
            ])
            ->toArray();
    }
}