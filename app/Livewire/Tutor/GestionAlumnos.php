<?php

namespace App\Livewire\Tutor;

use App\Models\Alerta;
use App\Models\Alumno;
use App\Notifications\AsignadoTutor;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class GestionAlumnos extends Component
{
    public string $busqueda          = '';
    public string $busquedaSinAsignar = '';
    public bool   $mostrarAsignar    = false;

    public function asignar(int $alumnoId): void
    {
        $tutor  = auth()->user()->tutor;
        $alumno = Alumno::with('usuario')->findOrFail($alumnoId);

        if ($alumno->tutor_id !== null) {
            $this->dispatch('toast', tipo: 'error', mensaje: 'Este alumno ya tiene un tutor asignado.');
            return;
        }

        $alumno->update(['tutor_id' => $tutor->id]);

        if ($alumno->usuario) {
            $alumno->usuario->notify(new AsignadoTutor($tutor->load('usuario')));
        }

        $this->busquedaSinAsignar = '';
        $this->dispatch('toast', tipo: 'success',
            mensaje: "Alumno {$alumno->usuario?->name} asignado correctamente.");
    }

    public function desasignar(int $alumnoId): void
    {
        $tutor  = auth()->user()->tutor;
        $alumno = Alumno::findOrFail($alumnoId);

        if ($alumno->tutor_id !== $tutor->id) {
            $this->dispatch('toast', tipo: 'error', mensaje: 'No puedes desasignar a este alumno.');
            return;
        }

        $alumno->update(['tutor_id' => null]);
        $this->dispatch('toast', tipo: 'info', mensaje: 'Alumno desasignado del grupo.');
    }

    public function render()
    {
        $tutor = auth()->user()->tutor;

        $asignados = $tutor->alumnosAsignados()
            ->with(['usuario:id,name,email', 'carrera:id,nombre,clave'])
            ->when($this->busqueda !== '', function ($q) {
                $busq = $this->busqueda;
                $q->where(fn($q) => $q
                    ->whereHas('usuario', fn($u) => $u->where('name', 'like', "%{$busq}%"))
                    ->orWhere('matricula', 'like', "%{$busq}%")
                );
            })
            ->orderBy('id')
            ->get();

        $sinAsignar = collect();
        if ($this->mostrarAsignar) {
            $sinAsignar = Alumno::whereNull('tutor_id')
                ->conCuenta()
                ->with(['usuario:id,name,email', 'carrera:id,nombre,clave'])
                ->when($this->busquedaSinAsignar !== '', function ($q) {
                    $busq = $this->busquedaSinAsignar;
                    $q->where(fn($q) => $q
                        ->whereHas('usuario', fn($u) => $u->where('name', 'like', "%{$busq}%"))
                        ->orWhere('matricula', 'like', "%{$busq}%")
                    );
                })
                ->limit(20)
                ->get();
        }

        $alertasPorAlumno = $asignados->isNotEmpty()
            ? Alerta::whereIn('alumno_id', $asignados->pluck('id'))
                ->where('atendida', false)
                ->selectRaw('alumno_id, count(*) as total')
                ->groupBy('alumno_id')
                ->pluck('total', 'alumno_id')
            : collect();

        return view('livewire.tutor.gestion-alumnos', compact(
            'asignados', 'sinAsignar', 'alertasPorAlumno'
        ));
    }
}
