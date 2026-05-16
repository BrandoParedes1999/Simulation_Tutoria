<?php

namespace App\Notifications;

use App\Models\Tutor;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AsignadoTutor extends Notification
{
    use Queueable;

    public function __construct(public readonly Tutor $tutor) {}

    public function via(mixed $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $grado  = $this->tutor->grado_academico ?? 'Dr.';
        $nombre = $this->tutor->usuario->name;

        return (new MailMessage)
            ->subject('Asignación a grupo de tutoría — Sistema de Tutoría')
            ->greeting("Hola, {$notifiable->name}")
            ->line("Has sido asignado al grupo de tutoría del {$grado} {$nombre}.")
            ->line('Ahora puedes comunicarte con tu tutor directamente desde el sistema.')
            ->action('Ver mi dashboard', url('/alumno/dashboard'))
            ->line('Si tienes dudas sobre tu asignación, contacta a control escolar.');
    }

    public function toDatabase(mixed $notifiable): array
    {
        $grado  = $this->tutor->grado_academico ?? 'Dr.';
        $nombre = $this->tutor->usuario->name;

        return [
            'tipo'         => 'asignacion_tutor',
            'titulo'       => 'Asignado a grupo de tutoría',
            'mensaje'      => "Fuiste asignado con el {$grado} {$nombre}",
            'tutor_id'     => $this->tutor->id,
            'tutor_nombre' => "{$grado} {$nombre}",
        ];
    }
}
