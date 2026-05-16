<?php

namespace App\Livewire\Alumno;

use App\Exceptions\InscripcionException;
use App\Models\Inscripcion;
use App\Models\Periodo;
use App\Services\InscripcionService;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Materias extends Component
{
    public string $tab            = 'disponibles';
    public array  $carrito        = [];
    public string $busqueda       = '';
    public ?int   $filtroSemestre = null;

    public function mount(): void { $this->tab = 'disponibles'; }

    public function cambiarTab(string $tab): void
    {
        if (!in_array($tab, ['disponibles', 'carrito', 'inscritas'])) return;
        $this->tab = $tab;
    }

    public function agregarAlCarrito(int $materiaId): void
    {
        if (!in_array($materiaId, $this->carrito, true)) {
            $this->carrito[] = $materiaId;
            $this->dispatch('toast', tipo: 'success', mensaje: 'Materia agregada al carrito');
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
        $alumno = auth()->user()?->alumno;
        if (!$alumno) return;
        try {
            $ids    = app(InscripcionService::class)
                ->obtenerMateriasDisponibles($alumno)
                ->where('semestre', $alumno->semestre_actual)
                ->pluck('id')->toArray();
            $nuevas = 0;
            foreach ($ids as $id) {
                if (!in_array($id, $this->carrito, true)) {
                    $this->carrito[] = $id;
                    $nuevas++;
                }
            }
            $this->dispatch('toast', tipo: 'success', mensaje: "{$nuevas} materias agregadas");
        } catch (\Throwable) {
            $this->dispatch('toast', tipo: 'error', mensaje: 'No se pudieron cargar las sugeridas.');
        }
    }

    public function confirmarInscripcion(): void
    {
        if (empty($this->carrito)) {
            $this->dispatch('toast', tipo: 'error', mensaje: 'El carrito está vacío');
            return;
        }
        try {
            $alumno        = auth()->user()->alumno;
            $inscripciones = app(InscripcionService::class)->inscribir($alumno, $this->carrito);
            $this->invalidarCache($alumno->id);
            $this->carrito = [];
            $this->tab     = 'inscritas';
            $count         = $inscripciones->count();
            $this->dispatch('toast', tipo: 'success',
                mensaje: "¡Inscripción exitosa! {$count} " . ($count === 1 ? 'materia inscrita' : 'materias inscritas')
            );
        } catch (InscripcionException $e) {
            $this->dispatch('toast', tipo: 'error', mensaje: $e->getMessage());
        }
    }

    public function darDeBaja(int $inscripcionId): void
    {
        try {
            $alumno      = auth()->user()->alumno;
            $inscripcion = Inscripcion::findOrFail($inscripcionId);
            app(InscripcionService::class)->darDeBaja($inscripcion, $alumno);
            $this->invalidarCache($alumno->id);
            $this->dispatch('toast', tipo: 'success', mensaje: 'Materia dada de baja');
        } catch (InscripcionException $e) {
            $this->dispatch('toast', tipo: 'error', mensaje: $e->getMessage());
        }
    }

    private function invalidarCache(int $alumnoId): void
    {
        Cache::forget("mats_disp_{$alumnoId}");
        Cache::forget("mats_ins_{$alumnoId}");
    }

    public function limpiarFiltros(): void
    {
        $this->busqueda       = '';
        $this->filtroSemestre = null;
    }

    public function render()
    {
        // ── Guardia principal ─────────────────────────────────────────
        $alumno = auth()->user()?->alumno;

        $vacio = [
            'alumno'                   => null,
            'periodo'                  => null,
            'periodoAbierto'           => false,
            'diasRestantesInscripcion' => 0,
            'disponibles'              => collect(),
            'sugeridas'                => collect(),
            'semestresDisponibles'     => [],
            'materiasEnCarrito'        => collect(),
            'creditosCarrito'          => 0,
            'erroresCarrito'           => [],
            'inscritas'                => collect(),
        ];

        if (!$alumno) {
            return view('livewire.alumno.materias', $vacio);
        }

        $servicio = app(InscripcionService::class);
        $periodo  = Cache::remember('periodo_actual', 60, fn() =>
            Periodo::where('es_actual', true)->first()
        );

        // ── Materias disponibles (cacheado 60s para respuesta rápida en cambio de pestaña) ──
        try {
            $todasDisponibles = Cache::remember(
                "mats_disp_{$alumno->id}",
                60,
                fn() => $servicio->obtenerMateriasDisponibles($alumno)
            );
        } catch (\Throwable) {
            $todasDisponibles = collect();
        }

        // ── Filtros en memoria ────────────────────────────────────────
        $sugeridas = $todasDisponibles->where('semestre', $alumno->semestre_actual)->values();

        $semestresDisponibles = $todasDisponibles
            ->pluck('semestre')->unique()->sort()->values()->toArray();

        $disponibles = $todasDisponibles;
        if ($this->busqueda) {
            $q           = mb_strtolower($this->busqueda);
            $disponibles = $disponibles->filter(fn($m) =>
                str_contains(mb_strtolower($m['nombre']), $q) ||
                str_contains(mb_strtolower($m['clave']), $q)
            );
        }
        if ($this->filtroSemestre) {
            $disponibles = $disponibles->where('semestre', $this->filtroSemestre);
        }
        $disponibles = $disponibles->values();

        // ── Carrito ───────────────────────────────────────────────────
        $materiasEnCarrito = empty($this->carrito)
            ? collect()
            : $todasDisponibles->whereIn('id', $this->carrito)->values();

        $creditosCarrito = $materiasEnCarrito->sum('creditos');

        try {
            $erroresCarrito = empty($this->carrito)
                ? []
                : $servicio->validarCarrito($alumno, $this->carrito);
        } catch (\Throwable) {
            $erroresCarrito = [];
        }

        // ── Inscritas (cacheado 60s) ──────────────────────────────────
        try {
            $inscritas = $periodo
                ? Cache::remember(
                    "mats_ins_{$alumno->id}",
                    60,
                    fn() => $servicio->obtenerMateriasInscritas($alumno, $periodo)
                )
                : collect();
        } catch (\Throwable) {
            $inscritas = collect();
        }

        // ── Fechas ────────────────────────────────────────────────────
        $periodoAbierto           = $periodo?->estaAbiertoParaInscripcion() ?? false;
        $diasRestantesInscripcion = 0;

        if ($periodo?->fecha_limite_inscripcion) {
            $diasRestantesInscripcion = max(0, (int) now()->startOfDay()
                ->diffInDays($periodo->fecha_limite_inscripcion->startOfDay(), false));
        }

        return view('livewire.alumno.materias', compact(
            'alumno', 'periodo', 'periodoAbierto', 'diasRestantesInscripcion',
            'disponibles', 'sugeridas', 'semestresDisponibles',
            'materiasEnCarrito', 'creditosCarrito', 'erroresCarrito', 'inscritas'
        ));
    }
}