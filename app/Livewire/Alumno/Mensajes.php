<?php

namespace App\Livewire\Alumno;

use App\Models\Mensaje;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Mensajes extends Component
{
    public ?int  $conversacionActivaId = null;
    public string $textoRespuesta = '';
    public string $pestana = 'recibidos';

    public function seleccionar(int $mensajeId): void
    {
        $this->conversacionActivaId = $mensajeId;
        $this->textoRespuesta = '';

        // Marcar como leído si el destinatario es el alumno actual
        $mensaje = Mensaje::find($mensajeId);
        if ($mensaje && $mensaje->destinatario_id === auth()->id() && !$mensaje->leido_en) {
            $mensaje->update(['leido_en' => now()]);
        }
    }

    public function responder(): void
    {
        if (!$this->conversacionActivaId || !trim($this->textoRespuesta)) return;

        $mensaje = Mensaje::find($this->conversacionActivaId);
        if (!$mensaje) return;

        $userId = auth()->id();

        if ($mensaje->remitente_id !== $userId && $mensaje->destinatario_id !== $userId) {
            $this->dispatch('toast', tipo: 'error', mensaje: 'No tienes permiso para responder este mensaje.');
            return;
        }

        $destinatarioId = $mensaje->remitente_id === $userId
            ? $mensaje->destinatario_id
            : $mensaje->remitente_id;

        Mensaje::create([
            'remitente_id'     => $userId,
            'destinatario_id'  => $destinatarioId,
            'tipo_destinatario'=> 'individual',
            'asunto'           => 'Re: ' . $mensaje->asunto,
            'contenido'        => trim($this->textoRespuesta),
            'prioridad'        => $mensaje->prioridad,
            'mensaje_padre_id' => $this->conversacionActivaId,
        ]);

        $this->textoRespuesta = '';
        $this->dispatch('toast', tipo: 'success', mensaje: 'Respuesta enviada');
    }

    public function render()
    {
        $userId = auth()->id();

        $conversaciones = Mensaje::where(function ($q) use ($userId) {
                $q->where('remitente_id', $userId)
                  ->orWhere('destinatario_id', $userId);
            })
            ->whereNull('mensaje_padre_id')
            ->with([
                'remitente:id,name',
                'destinatario:id,name',
                'respuestas' => fn ($q) => $q->orderBy('created_at'),
                'respuestas.remitente:id,name',
            ])
            ->orderByDesc('created_at')
            ->get();

        // Filtrar pestañas
        $filtradas = match ($this->pestana) {
            'enviados' => $conversaciones->filter(fn ($m) => $m->remitente_id === $userId),
            'urgentes' => $conversaciones->filter(fn ($m) => $m->prioridad === 'urgente'),
            default    => $conversaciones->filter(fn ($m) => $m->destinatario_id === $userId),
        };

        $noLeidos       = $conversaciones->filter(fn ($m) => $m->destinatario_id === $userId && !$m->leido_en)->count();
        $conversacionActiva = $this->conversacionActivaId
            ? $conversaciones->firstWhere('id', $this->conversacionActivaId)
            : null;

        return view('livewire.alumno.mensajes', [
            'conversaciones'    => $filtradas->values(),
            'conversacionActiva'=> $conversacionActiva,
            'noLeidos'          => $noLeidos,
            'userId'            => $userId,
        ]);
    }
}