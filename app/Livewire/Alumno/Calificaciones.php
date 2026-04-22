<?php

namespace App\Livewire\Alumno;

use App\Exceptions\CalificacionException;
use App\Models\Inscripcion;
use App\Services\CalificacionService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Calificaciones extends Component
{
    public array $edits = [];
    public ?int $abierta = null;

    public function abrir(int $inscripcionId): void
    {
        $alumno = auth()->user()->alumno;
        $materias = app(CalificacionService::class)->obtenerMateriasParaCapturar($alumno);

        $materia = $materias->firstWhere('id', $inscripcionId);
        if ($materia) {
            $this->edits[$inscripcionId] = [
                'parcial1' => $materia['parcial1'],
                'parcial2' => $materia['parcial2'],
                'parcial3' => $materia['parcial3'],
            ];
        }
        $this->abierta = $inscripcionId;
    }

    public function cerrar(): void
    {
        if ($this->abierta !== null) {
            unset($this->edits[$this->abierta]);
        }
        $this->abierta = null;
    }

    public function guardar(int $inscripcionId): void
    {
        if (!isset($this->edits[$inscripcionId])) {
            $this->dispatch('toast', tipo: 'error', mensaje: 'No hay cambios para guardar');
            return;
        }

        try {
            $inscripcion = Inscripcion::findOrFail($inscripcionId);
            $alumno = auth()->user()->alumno;

            app(CalificacionService::class)->guardar(
                $inscripcion,
                $alumno,
                $this->edits[$inscripcionId]
            );

            unset($this->edits[$inscripcionId]);
            $this->abierta = null;

            $this->dispatch('toast', tipo: 'success', mensaje: 'Calificaciones guardadas');
        } catch (CalificacionException $e) {
            $this->dispatch('toast', tipo: 'error', mensaje: $e->getMessage());
        }
    }

    public function render()
    {
        $alumno = auth()->user()->alumno;
        $servicio = app(CalificacionService::class);

        // UNA sola llamada que cachea internamente. Luego 'resumen' reusa el cache.
        $materias = $servicio->obtenerMateriasParaCapturar($alumno);
        $resumen  = $servicio->obtenerResumenPeriodo($alumno);

        return view('livewire.alumno.calificaciones', [
            'materias' => $materias,
            'resumen'  => $resumen,
        ]);
    }
}