<?php

namespace App\Livewire\Alumno;

use App\Exceptions\InscripcionException;
use App\Models\Inscripcion;
use App\Models\Periodo;
use App\Services\InscripcionService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Materias extends Component
{
    public string $tab = 'disponibles';
    public array $carrito = [];
    public string $busqueda = '';
    public ?int $filtroSemestre = null;

    public function mount(): void
    {
        $this->tab = 'disponibles';
    }

    public function cambiarTab(string $tab): void
    {
        if (!in_array($tab, ['disponibles', 'carrito', 'inscritas'])) return;
        $this->tab = $tab;
    }

    public function agregarAlCarrito(int $materiaId): void
    {
        if (!in_array($materiaId, $this->carrito, true)) {
            $this->carrito[] = $materiaId;
            $this->dispatch('toast', tipo: 'success', mensaje: 'Agregada al carrito');
        }
    }

    public function quitarDelCarrito(int $materiaId): void
    {
        $this->carrito = array_values(array_filter($this->carrito, fn($id) => $id !== $materiaId));
        $this->dispatch('toast', tipo: 'info', mensaje: 'Materia removida');
    }

    public function vaciarCarrito(): void
    {
        $this->carrito = [];
        $this->dispatch('toast', tipo: 'info', mensaje: 'Carrito vaciado');
    }

    public function agregarSugeridas(): void
    {
        $alumno = auth()->user()->alumno;
        $servicio = app(InscripcionService::class);

        $sugeridasIds = $servicio->obtenerMateriasDisponibles($alumno)
            ->where('semestre', $alumno->semestre_actual)
            ->pluck('id')
            ->toArray();

        $nuevas = 0;
        foreach ($sugeridasIds as $id) {
            if (!in_array($id, $this->carrito, true)) {
                $this->carrito[] = $id;
                $nuevas++;
            }
        }

        $this->dispatch('toast', tipo: 'success', mensaje: "{$nuevas} materias agregadas");
    }

    public function confirmarInscripcion(): void
    {
        if (empty($this->carrito)) {
            $this->dispatch('toast', tipo: 'error', mensaje: 'El carrito está vacío');
            return;
        }

        try {
            $alumno = auth()->user()->alumno;
            $inscripciones = app(InscripcionService::class)
                ->inscribir($alumno, $this->carrito);

            $this->carrito = [];
            $this->tab = 'inscritas';

            $count = $inscripciones->count();
            $this->dispatch(
                'toast',
                tipo: 'success',
                mensaje: "¡Inscripción exitosa! {$count} " . ($count === 1 ? 'materia inscrita' : 'materias inscritas')
            );
        } catch (InscripcionException $e) {
            $this->dispatch('toast', tipo: 'error', mensaje: $e->getMessage());
        }
    }

    public function darDeBaja(int $inscripcionId): void
    {
        try {
            $inscripcion = Inscripcion::findOrFail($inscripcionId);
            $alumno = auth()->user()->alumno;
            app(InscripcionService::class)->darDeBaja($inscripcion, $alumno);

            $this->dispatch('toast', tipo: 'success', mensaje: 'Materia dada de baja');
        } catch (InscripcionException $e) {
            $this->dispatch('toast', tipo: 'error', mensaje: $e->getMessage());
        }
    }

    public function limpiarFiltros(): void
    {
        $this->busqueda = '';
        $this->filtroSemestre = null;
    }

    public function render()
    {
        $alumno = auth()->user()->alumno;
        $servicio = app(InscripcionService::class);
        $periodo = Periodo::where('es_actual', true)->first();

        // UNA sola llamada al servicio para todas las disponibles
        $todasDisponibles = $servicio->obtenerMateriasDisponibles($alumno);

        // Derivamos todo de esa colección en memoria (sin más queries)
        $sugeridas = $todasDisponibles
            ->where('semestre', $alumno->semestre_actual)
            ->values();

        $semestresDisponibles = $todasDisponibles
            ->pluck('semestre')
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        // Aplicar filtros
        $disponibles = $todasDisponibles;
        if ($this->busqueda) {
            $q = mb_strtolower($this->busqueda);
            $disponibles = $disponibles->filter(fn($m) =>
                str_contains(mb_strtolower($m['nombre']), $q) ||
                str_contains(mb_strtolower($m['clave']), $q)
            );
        }
        if ($this->filtroSemestre) {
            $disponibles = $disponibles->where('semestre', $this->filtroSemestre);
        }
        $disponibles = $disponibles->values();

        // Materias en carrito (desde la misma colección ya cargada)
        $materiasEnCarrito = empty($this->carrito)
            ? collect()
            : $todasDisponibles->whereIn('id', $this->carrito)->values();

        $creditosCarrito = $materiasEnCarrito->sum('creditos');

        // Errores del carrito
        $erroresCarrito = empty($this->carrito)
            ? []
            : $servicio->validarCarrito($alumno, $this->carrito);

        // Inscritas del periodo
        $inscritas = $servicio->obtenerMateriasInscritas($alumno, $periodo);

        $periodoAbierto = $periodo?->estaAbiertoParaInscripcion() ?? false;
        $diasRestantesInscripcion = $periodo
            ? max(0, (int) now()->startOfDay()->diffInDays($periodo->fecha_limite_inscripcion->startOfDay(), false))
            : 0;

        return view('livewire.alumno.materias', [
            'alumno'                    => $alumno,
            'periodo'                   => $periodo,
            'periodoAbierto'            => $periodoAbierto,
            'diasRestantesInscripcion'  => $diasRestantesInscripcion,
            'disponibles'               => $disponibles,
            'sugeridas'                 => $sugeridas,
            'semestresDisponibles'      => $semestresDisponibles,
            'materiasEnCarrito'         => $materiasEnCarrito,
            'creditosCarrito'           => $creditosCarrito,
            'erroresCarrito'            => $erroresCarrito,
            'inscritas'                 => $inscritas,
        ]);
    }
}