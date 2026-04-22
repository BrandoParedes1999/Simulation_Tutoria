<?php

namespace App\Livewire\Alumno;

use App\Models\Alerta;
use App\Models\Periodo;
use App\Services\ElegibilidadService;
use App\Services\MallaCurricularService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public function render()
    {
        $alumno = auth()->user()->alumno;
        $alumno->load('carrera', 'tutor.usuario');

        $periodo = Periodo::where('es_actual', true)->first();
        $servicio = app(ElegibilidadService::class);

        $elegibilidad    = $servicio->evaluar($alumno);
        $evolucion       = $servicio->obtenerEvolucionPromedio($alumno);
        $datosPeriodo    = $servicio->obtenerDatosPeriodo($alumno, $periodo);
        $mensajesRecientes = $servicio->obtenerMensajesRecientes($alumno);
        $estadisticas    = app(MallaCurricularService::class)->obtenerEstadisticas($alumno);

        $alertas = Alerta::where('alumno_id', $alumno->id)
            ->where('atendida', false)
            ->orderByRaw("FIELD(prioridad, 'critica', 'media', 'baja')")
            ->limit(3)
            ->get();

        $alertasTotal = Alerta::where('alumno_id', $alumno->id)
            ->where('atendida', false)
            ->count();

        // Clasificación del promedio
        $clasificacionPromedio = match(true) {
            $datosPeriodo['promedio_semestral'] >= 90 => ['texto' => 'Excelente', 'color' => 'text-emerald-600'],
            $datosPeriodo['promedio_semestral'] >= 70 => ['texto' => 'Buen rendimiento', 'color' => 'text-blue-600'],
            $datosPeriodo['promedio_semestral'] > 0   => ['texto' => 'Requiere atención', 'color' => 'text-red-600'],
            default => ['texto' => 'Sin calificaciones aún', 'color' => 'text-blue-400'],
        };

        // Semestres restantes estimados (9 semestres total en ISC 2010)
        $semestresRestantes = max(0, 9 - $alumno->semestre_actual);

        return view('livewire.alumno.dashboard', [
            'alumno'              => $alumno,
            'periodo'             => $periodo,
            'elegibilidad'        => $elegibilidad,
            'evolucion'           => $evolucion,
            'datosPeriodo'        => $datosPeriodo,
            'estadisticas'        => $estadisticas,
            'alertas'             => $alertas,
            'alertasTotal'        => $alertasTotal,
            'mensajesRecientes'   => $mensajesRecientes,
            'clasificacionPromedio' => $clasificacionPromedio,
            'semestresRestantes'  => $semestresRestantes,
        ]);
    }
}