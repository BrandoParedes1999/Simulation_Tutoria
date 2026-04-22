<?php

namespace App\Livewire\Alumno;

use App\Models\Inscripcion;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Historial extends Component
{
    public string $filtroEstatus = 'todos';
    public ?int   $periodoDetalle = null;

    public function render()
    {
        $alumno = auth()->user()->alumno;

        // Una sola query con eager loading
        $todasInscripciones = $alumno->inscripciones()
            ->with(['materiaMalla:id,clave,nombre,creditos,semestre,area', 'periodo:id,clave,nombre,fecha_inicio'])
            ->whereIn('estatus', ['aprobada', 'reprobada', 'dada_de_baja'])
            ->orderByDesc('periodo_id')
            ->get();

        // Filtrar por estatus si aplica
        $inscripciones = $this->filtroEstatus === 'todos'
            ? $todasInscripciones
            : $todasInscripciones->where('estatus', $this->filtroEstatus);

        // Agrupar por periodo
        $historial = $inscripciones->groupBy(fn($i) => $i->periodo?->clave ?? 'Sin periodo');

        // Resumen global calculado siempre sobre TODAS (sin filtro)
        $resumen = [
            'total_materias'    => $todasInscripciones->count(),
            'aprobadas'         => $todasInscripciones->where('estatus', 'aprobada')->count(),
            'reprobadas'        => $todasInscripciones->where('estatus', 'reprobada')->count(),
            'dadas_de_baja'     => $todasInscripciones->where('estatus', 'dada_de_baja')->count(),
            'creditos_aprobados'=> (int) $alumno->creditos_aprobados,
            'promedio_general'  => (float) $alumno->promedio_general,
        ];

        return view('livewire.alumno.historial', [
            'historial' => $historial,
            'resumen'   => $resumen,
        ]);
    }
}