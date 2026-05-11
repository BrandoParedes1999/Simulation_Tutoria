<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Alerta;
use App\Models\Alumno;
use App\Models\Inscripcion;
use App\Models\Periodo;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function index(Request $request)
    {
        $tutor         = auth()->user()->tutor;
        $alumnos       = $tutor->alumnosAsignados->load('usuario', 'carrera');
        $periodos      = Periodo::orderByDesc('fecha_inicio')->get();
        $periodoActual = $periodos->firstWhere('es_actual', true);

        // Sin parámetro tipo → mostrar el formulario wizard
        if (!$request->filled('tipo')) {
            return view('tutor.reportes', compact('tutor', 'alumnos', 'periodos', 'periodoActual'));
        }

        return match ($request->tipo) {
            'individual'  => $this->individual($request, $tutor),
            'grupal'      => $this->grupal($request, $tutor, $alumnos),
            'comparativo' => $this->comparativo($request, $tutor, $alumnos),
            default       => back()->withErrors(['tipo' => 'Tipo de reporte no válido.']),
        };
    }

    // ── Individual ────────────────────────────────────────────────────────────

    private function individual(Request $request, $tutor)
    {
        $alumno = Alumno::with(['usuario', 'carrera'])
            ->where('tutor_id', $tutor->id)
            ->findOrFail($request->integer('alumno_id'));

        $periodo = Periodo::findOrFail($request->integer('periodo_id'));

        $inscripciones = Inscripcion::where('alumno_id', $alumno->id)
            ->where('periodo_id', $periodo->id)
            ->with('materiaMalla:id,clave,nombre,creditos')
            ->orderBy('created_at')
            ->get();

        $alertas = Alerta::where('alumno_id', $alumno->id)
            ->where('atendida', false)
            ->orderByRaw("FIELD(prioridad,'critica','media','baja')")
            ->get();

        $evolucion = DB::table('inscripciones')
            ->join('periodos', 'inscripciones.periodo_id', '=', 'periodos.id')
            ->where('inscripciones.alumno_id', $alumno->id)
            ->whereIn('inscripciones.estatus', ['aprobada', 'reprobada'])
            ->whereNotNull('inscripciones.promedio')
            ->groupBy('periodos.id', 'periodos.clave', 'periodos.fecha_inicio')
            ->orderBy('periodos.fecha_inicio')
            ->select('periodos.clave', DB::raw('ROUND(AVG(inscripciones.promedio),2) as promedio'))
            ->get();

        // csv → descarga directa; pdf/word → HTML imprimible
        if ($request->formato === 'csv') {
            return $this->csvIndividual($alumno, $periodo, $inscripciones);
        }

        return view('tutor.reportes.individual',
            compact('tutor', 'alumno', 'periodo', 'inscripciones', 'alertas', 'evolucion'));
    }

    // ── Grupal ────────────────────────────────────────────────────────────────

    private function grupal(Request $request, $tutor, $alumnos)
    {
        $periodo = Periodo::findOrFail($request->integer('periodo_id'));
        $ids     = $alumnos->pluck('id');

        $inscripciones = Inscripcion::whereIn('alumno_id', $ids)
            ->where('periodo_id', $periodo->id)
            ->with(['alumno.usuario:id,name', 'materiaMalla:id,nombre,clave,creditos'])
            ->get();

        $alertasPorAlumno = Alerta::whereIn('alumno_id', $ids)
            ->where('atendida', false)
            ->selectRaw('alumno_id, count(*) as total')
            ->groupBy('alumno_id')
            ->pluck('total', 'alumno_id');

        if ($request->formato === 'csv') {
            return $this->csvGrupal($alumnos, $periodo);
        }

        $opciones = [
            'distribucion' => $request->boolean('incluir_distribucion'),
            'alertas'      => $request->boolean('incluir_alertas'),
            'detalle'      => $request->boolean('incluir_detalle'),
        ];

        return view('tutor.reportes.grupal',
            compact('tutor', 'alumnos', 'periodo', 'inscripciones', 'alertasPorAlumno', 'opciones'));
    }

    // ── Comparativo ───────────────────────────────────────────────────────────

    private function comparativo(Request $request, $tutor, $alumnos)
    {
        $desdeId = $request->integer('periodo_desde');
        $hastaId = $request->integer('periodo_hasta');

        if (!$desdeId || !$hastaId) {
            return back()->withErrors(['periodo' => 'Selecciona ambos periodos para el reporte comparativo.']);
        }

        $periodos = Periodo::whereIn('id', [$desdeId, $hastaId])
            ->orderBy('fecha_inicio')
            ->get();

        $ids = $alumnos->pluck('id');

        $datos = $periodos->map(function ($p) use ($ids) {
            $insc = Inscripcion::whereIn('alumno_id', $ids)
                ->where('periodo_id', $p->id)
                ->whereNotNull('promedio')
                ->get();

            return [
                'periodo'    => $p,
                'promedio'   => round((float) ($insc->where('promedio', '>', 0)->avg('promedio') ?? 0), 2),
                'aprobadas'  => $insc->where('estatus', 'aprobada')->count(),
                'reprobadas' => $insc->where('estatus', 'reprobada')->count(),
                'en_curso'   => $insc->where('estatus', 'en_curso')->count(),
            ];
        });

        if ($request->formato === 'csv') {
            return $this->csvComparativo($datos);
        }

        return view('tutor.reportes.comparativo',
            compact('tutor', 'alumnos', 'periodos', 'datos'));
    }

    // ── CSV helpers ───────────────────────────────────────────────────────────

    private function csvIndividual($alumno, $periodo, $inscripciones): Response
    {
        $nombre   = preg_replace('/[^a-zA-Z0-9_]/', '_', $alumno->usuario->name ?? 'alumno');
        $filename = "reporte_individual_{$nombre}_{$periodo->clave}.csv";

        $csv  = "\xEF\xBB\xBF"; // BOM UTF-8 para Excel
        $csv .= "REPORTE INDIVIDUAL\n";
        $csv .= "Alumno,{$alumno->usuario->name}\n";
        $csv .= "Matrícula,{$alumno->matricula}\n";
        $csv .= "Carrera,\"" . ($alumno->carrera->nombre ?? '') . "\"\n";
        $csv .= "Semestre,{$alumno->semestre_actual}\n";
        $csv .= "Promedio General," . number_format((float) $alumno->promedio_general, 1) . "\n";
        $csv .= "Periodo,\"{$periodo->nombre}\"\n\n";
        $csv .= "Clave,Materia,Créditos,P1,P2,P3,Promedio,Estatus\n";

        foreach ($inscripciones as $i) {
            $csv .= implode(',', [
                $i->materiaMalla->clave    ?? '',
                '"' . ($i->materiaMalla->nombre ?? '') . '"',
                $i->materiaMalla->creditos ?? 0,
                $i->parcial1 !== null ? number_format($i->parcial1, 1) : '',
                $i->parcial2 !== null ? number_format($i->parcial2, 1) : '',
                $i->parcial3 !== null ? number_format($i->parcial3, 1) : '',
                $i->promedio  !== null ? number_format($i->promedio,  1) : '',
                $i->estatus,
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function csvGrupal($alumnos, $periodo): Response
    {
        $filename = "reporte_grupal_{$periodo->clave}.csv";

        $csv  = "\xEF\xBB\xBF";
        $csv .= "REPORTE GRUPAL\n";
        $csv .= "Periodo,\"{$periodo->nombre}\"\n\n";
        $csv .= "Matrícula,Nombre,Semestre,Promedio General,Créditos Aprobados\n";

        foreach ($alumnos as $a) {
            $csv .= implode(',', [
                $a->matricula,
                '"' . ($a->usuario->name ?? '') . '"',
                $a->semestre_actual,
                number_format((float) $a->promedio_general, 1),
                $a->creditos_aprobados ?? 0,
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function csvComparativo($datos): Response
    {
        $filename = 'reporte_comparativo.csv';

        $csv  = "\xEF\xBB\xBF";
        $csv .= "REPORTE COMPARATIVO\n\n";
        $csv .= "Periodo,Promedio Grupal,Aprobadas,Reprobadas,En Curso\n";

        foreach ($datos as $d) {
            $csv .= implode(',', [
                $d['periodo']->clave,
                number_format($d['promedio'],   1),
                $d['aprobadas'],
                $d['reprobadas'],
                $d['en_curso'],
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}