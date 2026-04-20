<?php

namespace App\Livewire\Alumno;

use App\Services\MallaCurricularService;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;

#[Layout('layouts.app')]
class MallaCurricular extends Component
{
    public ?int $semestreSeleccionado = null;
    public ?array $materiaSeleccionada = null;

    public function mount()
    {
        $this->semestreSeleccionado = auth()->user()->alumno->semestre_actual;
    }

    /**
     * Usamos #[Computed(persist: true)] para que SÓLO se calcule una vez
     * por ciclo de vida del componente, no cada render.
     */
    #[Computed(persist: true, seconds: 300)]
    public function malla()
    {
        return app(MallaCurricularService::class)
            ->obtenerMallaDelAlumno(auth()->user()->alumno);
    }

    #[Computed(persist: true, seconds: 300)]
    public function estadisticas()
    {
        return app(MallaCurricularService::class)
            ->obtenerEstadisticas(auth()->user()->alumno);
    }

    public function seleccionarSemestre(int $semestre)
    {
        $this->semestreSeleccionado = $semestre;
        $this->materiaSeleccionada = null;
    }

    public function verDetalleMateria(array $materia)
    {
        $this->materiaSeleccionada = $materia;
    }

    public function cerrarDetalle()
    {
        $this->materiaSeleccionada = null;
    }

    public function render()
    {
        return view('livewire.alumno.malla-curricular');
    }
}