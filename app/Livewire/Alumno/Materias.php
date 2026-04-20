<?php

namespace App\Livewire\Alumno;

use App\Exceptions\InscripcionException;
use App\Models\Inscripcion;
use App\Models\Periodo;
use App\Services\InscripcionService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Materias extends Component
{
    /**
     * Tabs: 'disponibles' | 'carrito' | 'inscritas'
     */
    public string $tab = 'disponibles';

    /**
     * IDs de materias en el carrito (antes de confirmar inscripción)
     */
    public array $carrito = [];

    /**
     * Filtro de búsqueda por texto
     */
    public string $busqueda = '';

    /**
     * Filtro por semestre (null = todos)
     */
    public ?int $filtroSemestre = null;

    public function mount(): void
    {
        // Arranca en tab carrito si ya eligieron algo, si no, en disponibles
        $this->tab = 'disponibles';
    }

    // ─── Datos calculados (cache dentro del componente) ───

    #[Computed]
    public function alumno()
    {
        return auth()->user()->alumno;
    }

    #[Computed]
    public function periodo(): ?Periodo
    {
        return Periodo::where('es_actual', true)->first();
    }

    #[Computed]
    public function periodoAbierto(): bool
    {
        return $this->periodo?->estaAbiertoParaInscripcion() ?? false;
    }

    #[Computed]
    public function diasRestantesInscripcion(): int
    {
        if (!$this->periodo) return 0;
        return max(0, now()->diffInDays($this->periodo->fecha_limite_inscripcion, false));
    }

    #[Computed]
    public function disponibles()
    {
        $servicio = app(InscripcionService::class);
        $materias = $servicio->obtenerMateriasDisponibles($this->alumno);

        // Aplicar filtros
        if ($this->busqueda) {
            $q = mb_strtolower($this->busqueda);
            $materias = $materias->filter(fn($m) =>
                str_contains(mb_strtolower($m['nombre']), $q) ||
                str_contains(mb_strtolower($m['clave']), $q)
            );
        }

        if ($this->filtroSemestre) {
            $materias = $materias->where('semestre', $this->filtroSemestre);
        }

        return $materias->values();
    }

    #[Computed]
    public function inscritas()
    {
        return app(InscripcionService::class)
            ->obtenerMateriasInscritas($this->alumno, $this->periodo);
    }

    /**
     * Materias sugeridas: las del semestre actual del alumno que están disponibles.
     */
    #[Computed]
    public function sugeridas()
    {
        $servicio = app(InscripcionService::class);
        return $servicio->obtenerMateriasDisponibles($this->alumno)
            ->where('semestre', $this->alumno->semestre_actual)
            ->values();
    }

    #[Computed]
    public function materiasEnCarrito()
    {
        if (empty($this->carrito)) return collect();

        $servicio = app(InscripcionService::class);
        return $servicio->obtenerMateriasDisponibles($this->alumno)
            ->whereIn('id', $this->carrito)
            ->values();
    }

    #[Computed]
    public function creditosCarrito(): int
    {
        return $this->materiasEnCarrito->sum('creditos');
    }

    #[Computed]
    public function erroresCarrito(): array
    {
        if (empty($this->carrito)) return [];

        return app(InscripcionService::class)
            ->validarCarrito($this->alumno, $this->carrito);
    }

    #[Computed]
    public function semestresDisponibles(): array
    {
        $servicio = app(InscripcionService::class);
        return $servicio->obtenerMateriasDisponibles($this->alumno)
            ->pluck('semestre')
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }

    // ─── Acciones ──────────────────────────────────────

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

    /**
     * Agrega todas las sugeridas al carrito de una vez.
     */
    public function agregarSugeridas(): void
    {
        $nuevas = $this->sugeridas->pluck('id')->toArray();

        foreach ($nuevas as $id) {
            if (!in_array($id, $this->carrito, true)) {
                $this->carrito[] = $id;
            }
        }

        $count = count($nuevas);
        $this->dispatch('toast', tipo: 'success', mensaje: "{$count} materias agregadas");
    }

    public function confirmarInscripcion(): void
    {
        if (empty($this->carrito)) {
            $this->dispatch('toast', tipo: 'error', mensaje: 'El carrito está vacío');
            return;
        }

        try {
            $inscripciones = app(InscripcionService::class)
                ->inscribir($this->alumno, $this->carrito);

            $this->carrito = [];
            $this->tab = 'inscritas';

            // Refresca computed
            unset($this->disponibles, $this->inscritas, $this->sugeridas);

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
            app(InscripcionService::class)->darDeBaja($inscripcion, $this->alumno);

            unset($this->disponibles, $this->inscritas);

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
        return view('livewire.alumno.materias');
    }
}