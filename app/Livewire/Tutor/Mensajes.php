<?php

namespace App\Livewire\Tutor;

use App\Models\Alerta;
use App\Models\Mensaje;
use App\Models\User;
use App\Notifications\MensajeRecibido;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Mensajes extends Component
{
    public ?int  $conversacionActivaId = null;
    public string $textoRespuesta = '';
    public string $pestana = 'todo';

    public function mount(): void
    {
        if ($id = request()->query('conversacion')) {
            $this->seleccionar((int) $id);
        }
    }

    // Modal nueva notificación
    public bool   $modalAbierto          = false;
    public string $tipoDestinatario      = 'alumno';
    public ?int   $destinatarioId        = null;
    public string $asunto                = '';
    public string $contenido             = '';
    public string $prioridad             = 'normal';
    public string $plantillaSeleccionada = '';

    public function seleccionar(int $mensajeId): void
    {
        $this->conversacionActivaId = $mensajeId;
        $this->textoRespuesta = '';

        // Marcar como leídas todas las respuestas del alumno que llegaron al tutor
        $userId = auth()->id();
        Mensaje::where(function ($q) use ($mensajeId) {
            $q->where('id', $mensajeId)
              ->orWhere('mensaje_padre_id', $mensajeId);
        })
        ->where('destinatario_id', $userId)
        ->whereNull('leido_en')
        ->update(['leido_en' => now()]);
    }

    public function responder(): void
    {
        if (!$this->conversacionActivaId || !trim($this->textoRespuesta)) return;

        $mensaje = Mensaje::find($this->conversacionActivaId);
        if (!$mensaje) return;

        $userId = auth()->id();
        if ($mensaje->remitente_id !== $userId && $mensaje->destinatario_id !== $userId) return;

        $destinatarioId = $mensaje->remitente_id === $userId
            ? $mensaje->destinatario_id
            : $mensaje->remitente_id;

        $respuesta = Mensaje::create([
            'remitente_id'     => $userId,
            'destinatario_id'  => $destinatarioId,
            'tipo_destinatario'=> 'individual',
            'asunto'           => 'Re: ' . $mensaje->asunto,
            'contenido'        => trim($this->textoRespuesta),
            'prioridad'        => $mensaje->prioridad,
            'mensaje_padre_id' => $this->conversacionActivaId,
        ]);

        User::find($destinatarioId)?->notify(new MensajeRecibido($respuesta, auth()->user()->name));

        $this->textoRespuesta = '';
        $this->dispatch('toast', tipo: 'success', mensaje: 'Respuesta enviada.');
    }

    public function enviar(): void
    {
        $this->validate([
            'tipoDestinatario' => 'required|in:alumno,grupo',
            'asunto'           => 'required|max:255',
            'contenido'        => 'required|max:5000',
            'prioridad'        => 'required|in:urgente,normal,informativo',
            'destinatarioId'   => 'required_if:tipoDestinatario,alumno|nullable|exists:users,id',
        ]);

        $tutor           = auth()->user()->tutor;
        $userId          = auth()->id();
        $remitenteNombre = auth()->user()->name;

        if ($this->tipoDestinatario === 'alumno') {
            $msg = Mensaje::create([
                'remitente_id'     => $userId,
                'destinatario_id'  => $this->destinatarioId,
                'tipo_destinatario'=> 'individual',
                'asunto'           => $this->asunto,
                'contenido'        => $this->contenido,
                'prioridad'        => $this->prioridad,
                'plantilla_usada'  => $this->plantillaSeleccionada ?: null,
            ]);
            User::find($this->destinatarioId)?->notify(new MensajeRecibido($msg, $remitenteNombre));
        } else {
            $alumnos = $tutor->alumnosAsignados()->with('usuario:id,name')->get();
            foreach ($alumnos as $alumno) {
                if (!$alumno->usuario_id) continue;
                $msg = Mensaje::create([
                    'remitente_id'     => $userId,
                    'destinatario_id'  => $alumno->usuario_id,
                    'tipo_destinatario'=> 'grupal',
                    'asunto'           => $this->asunto,
                    'contenido'        => $this->contenido,
                    'prioridad'        => $this->prioridad,
                    'plantilla_usada'  => $this->plantillaSeleccionada ?: null,
                ]);
                $alumno->usuario?->notify(new MensajeRecibido($msg, $remitenteNombre));
            }
        }

        $this->reset('modalAbierto', 'asunto', 'contenido', 'plantillaSeleccionada', 'destinatarioId');
        $this->tipoDestinatario = 'alumno';
        $this->prioridad = 'normal';
        $this->dispatch('toast', tipo: 'success', mensaje: 'Mensaje enviado correctamente.');
    }

    public function usarPlantilla(string $clave): void
    {
        $map = [
            'bajo_rendimiento'    => ['Bajo rendimiento',      'Estimado alumno, he notado que tu promedio actual está por debajo del mínimo requerido. Es importante que nos reunamos para diseñar un plan de mejora.'],
            'plan_recuperacion'   => ['Plan de recuperación',  'Te invito a revisar juntos las materias en las que presentas dificultades y elaborar un plan de recuperación académica.'],
            'caida_calificacion'  => ['Caída en calificaciones','He detectado una caída en tus calificaciones recientes. Me gustaría hablar contigo para identificar las causas y buscar soluciones.'],
            'invitacion_asesoria' => ['Invitación a asesoría', 'Te invito a una sesión de asesoría para revisar tu situación académica. Por favor confirma tu disponibilidad.'],
            'recordatorio_grupal' => ['Recordatorio grupal',   'Estimado grupo, les recuerdo que estoy disponible para atender cualquier consulta académica. No duden en contactarme.'],
            'felicitacion'        => ['Felicitación',           '¡Felicitaciones por tu excelente desempeño académico! Sigue así, es un orgullo tenerte como alumno.'],
        ];

        if (isset($map[$clave])) {
            [$asunto, $contenido] = $map[$clave];
            $this->asunto                = $this->asunto ?: $asunto;
            $this->contenido             = $contenido;
            $this->plantillaSeleccionada = $clave;
        }
    }

    public function abrirModalParaAlumno(int $usuarioId, string $plantilla): void
    {
        $this->modalAbierto     = true;
        $this->tipoDestinatario = 'alumno';
        $this->destinatarioId   = $usuarioId;
        $this->asunto           = '';
        $this->contenido        = '';
        $this->usarPlantilla($plantilla);
    }

    public function render()
    {
        $userId = auth()->id();
        $tutor  = auth()->user()->tutor;

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
            ->get()
            ->map(function ($m) use ($userId) {
                $lastReply             = $m->respuestas->last();
                $m->ultima_actividad   = $lastReply ? $lastReply->created_at : $m->created_at;
                $m->ultimo_contenido   = \Str::limit($lastReply ? $lastReply->contenido : $m->contenido, 60);
                $m->ultimo_es_tutor    = $lastReply ? $lastReply->remitente_id === $userId : $m->remitente_id === $userId;
                // Respuestas del alumno no leídas por el tutor
                $m->sin_leer           = $m->respuestas
                    ->filter(fn ($r) => $r->remitente_id !== $userId && !$r->leido_en)
                    ->count() > 0;
                // Nombre de la otra persona
                $m->otro_nombre = $m->remitente_id === $userId
                    ? ($m->destinatario?->name ?? '—')
                    : ($m->remitente?->name ?? '—');
                return $m;
            })
            ->sortByDesc('ultima_actividad')
            ->values();

        $todo         = $conversaciones;
        $porResponder = $conversaciones->filter(fn ($m) => $m->sin_leer)->values();
        $urgentes     = $conversaciones->filter(fn ($m) => $m->prioridad === 'urgente')->values();

        $conversacionActiva = $this->conversacionActivaId
            ? $conversaciones->firstWhere('id', $this->conversacionActivaId)
            : null;

        $alumnos = $tutor->alumnosAsignados()->with('usuario:id,name', 'carrera:id,clave,nombre')->get();

        $alertasPorAlumno = Alerta::whereIn('alumno_id', $alumnos->pluck('id'))
            ->where('atendida', false)
            ->selectRaw('alumno_id, count(*) as total')
            ->groupBy('alumno_id')
            ->pluck('total', 'alumno_id');

        $alumnosCriticos = $alumnos->filter(
            fn ($a) => (float) $a->promedio_general > 0 && (float) $a->promedio_general < 75
        )->take(5);

        return view('livewire.tutor.mensajes', [
            'todo'              => $todo,
            'porResponder'      => $porResponder,
            'urgentes'          => $urgentes,
            'conversacionActiva'=> $conversacionActiva,
            'alumnos'           => $alumnos,
            'alertasPorAlumno'  => $alertasPorAlumno,
            'alumnosCriticos'   => $alumnosCriticos,
            'userId'            => $userId,
        ]);
    }
}
