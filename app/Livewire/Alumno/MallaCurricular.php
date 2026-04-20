<?php

namespace App\Livewire\Alumno;

use App\Services\MallaCurricularService;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class MallaCurricular extends Component
{
    public array $malla = [];
    public array $estadisticas = [];
    public int $semestreInicial = 1;

    public function mount()
    {
        $alumno = auth()->user()->alumno;
        $servicio = app(MallaCurricularService::class);

        // Carga todo una sola vez. No hay más interacción con el servidor.
        $this->malla = $servicio->obtenerMallaDelAlumno($alumno);
        $this->estadisticas = $servicio->obtenerEstadisticas($alumno);
        $this->semestreInicial = $alumno->semestre_actual ?? 1;
    }

    public function render()
    {
        return view('livewire.alumno.malla-curricular');
    }
}