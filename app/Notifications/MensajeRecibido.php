<?php

namespace App\Notifications;

use App\Models\Mensaje;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MensajeRecibido extends Notification
{
    use Queueable;

    public function __construct(public readonly Mensaje $mensaje, public readonly string $remitenteNombre) {}

    public function via(mixed $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $esRespuesta = $this->mensaje->mensaje_padre_id !== null;
        $accion      = $esRespuesta ? 'respondió a tu mensaje' : 'te envió un mensaje';

        return (new MailMessage)
            ->subject("[Tutoría] {$this->remitenteNombre} {$accion}: {$this->mensaje->asunto}")
            ->greeting("Hola, {$notifiable->name}")
            ->line("{$this->remitenteNombre} {$accion}.")
            ->line("**Asunto:** {$this->mensaje->asunto}")
            ->line("**Mensaje:** " . \Str::limit($this->mensaje->contenido, 200))
            ->action('Ver mensaje', url('/'))
            ->salutation('Sistema de Tutoría');
    }

    public function toDatabase(mixed $notifiable): array
    {
        $esRespuesta = $this->mensaje->mensaje_padre_id !== null;
        $accion      = $esRespuesta ? 'respondió tu mensaje' : 'te envió un mensaje';

        return [
            'tipo'             => 'mensaje_recibido',
            'titulo'           => "{$this->remitenteNombre} {$accion}",
            'mensaje'          => \Str::limit($this->mensaje->contenido, 120),
            'asunto'           => $this->mensaje->asunto,
            'mensaje_id'       => $this->mensaje->id,
            'remitente_nombre' => $this->remitenteNombre,
        ];
    }
}
