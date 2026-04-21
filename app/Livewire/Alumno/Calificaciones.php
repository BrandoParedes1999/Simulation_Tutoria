<?php

namespace App\Livewire\Alumno;

use App\Exceptions\CalificacionException;
use App\Models\Inscripcion;
use App\Services\CalificacionService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Calificaciones extends Component
{
    /**
     * Cache local de calificaciones editadas (no guardadas aún).
     * Formato: [inscripcion_id => ['parcial1' => valor, 'parcial2' => valor, 'parcial3' => valor]]
     */
    public array $edits = [];

    /**
     * ID de la inscripción actualmente abierta en edición
     */
    public ?int $abierta = null;

    #[Computed]
    public function alumno()
    {
        return auth()->user()->alumno;
    }

    #[Computed]
    public function materias()
    {
        return app(CalificacionService::class)
            ->obtenerMateriasParaCapturar($this->alumno);
    }

    #[Computed]
    public function resumen(): array
    {
        return app(CalificacionService::class)
            ->obtenerResumenPeriodo($this->alumno);
    }

    public function abrir(int $inscripcionId): void
    {
        // Cargar los valores actuales en el buffer
        $materia = $this->materias->firstWhere('id', $inscripcionId);
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

            app(CalificacionService::class)->guardar(
                $inscripcion,
                $this->alumno,
                $this->edits[$inscripcionId]
            );

            unset($this->edits[$inscripcionId]);
            $this->abierta = null;

            // Refrescar computed
            unset($this->materias, $this->resumen);

            $this->dispatch('toast', tipo: 'success', mensaje: 'Calificaciones guardadas');
        } catch (CalificacionException $e) {
            $this->dispatch('toast', tipo: 'error', mensaje: $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.alumno.calificaciones');
    }
}