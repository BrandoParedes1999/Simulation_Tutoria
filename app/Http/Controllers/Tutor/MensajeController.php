<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Mensaje;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MensajeController extends Controller
{
    /**
     * Enviar un mensaje individual o grupal desde el tutor.
     */
    public function enviar(Request $request): JsonResponse
    {
        $request->validate([
            'tipo'           => 'required|in:alumno,grupo',
            'asunto'         => 'required|string|max:255',
            'contenido'      => 'required|string|max:5000',
            'prioridad'      => 'required|in:urgente,normal,informativo',
            'destinatario_id'=> 'required_if:tipo,alumno|nullable|exists:users,id',
        ]);

        $tutor  = auth()->user()->tutor;
        $userId = auth()->id();

        if ($request->tipo === 'alumno') {
            Mensaje::create([
                'remitente_id'    => $userId,
                'destinatario_id' => $request->destinatario_id,
                'tipo_destinatario'=> 'individual',
                'asunto'          => $request->asunto,
                'contenido'       => $request->contenido,
                'prioridad'       => $request->prioridad,
                'plantilla_usada' => $request->plantilla,
            ]);
        } else {
            // Mensaje grupal: crea un Mensaje por cada alumno asignado
            $alumnos = $tutor->alumnosAsignados()->with('usuario:id')->get();

            foreach ($alumnos as $alumno) {
                Mensaje::create([
                    'remitente_id'    => $userId,
                    'destinatario_id' => $alumno->usuario_id,
                    'tipo_destinatario'=> 'grupal',
                    'asunto'          => $request->asunto,
                    'contenido'       => $request->contenido,
                    'prioridad'       => $request->prioridad,
                    'plantilla_usada' => $request->plantilla,
                ]);
            }
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Responder a una conversación existente.
     */
    public function responder(Request $request, Mensaje $mensaje): JsonResponse
    {
        $request->validate(['contenido' => 'required|string|max:5000']);

        $userId = auth()->id();

        // Solo quien participó en la conversación puede responder
        if ($mensaje->remitente_id !== $userId && $mensaje->destinatario_id !== $userId) {
            return response()->json(['ok' => false, 'error' => 'No autorizado'], 403);
        }

        $destinatarioId = $mensaje->remitente_id === $userId
            ? $mensaje->destinatario_id
            : $mensaje->remitente_id;

        Mensaje::create([
            'remitente_id'    => $userId,
            'destinatario_id' => $destinatarioId,
            'tipo_destinatario'=> 'individual',
            'asunto'          => 'Re: ' . $mensaje->asunto,
            'contenido'       => $request->contenido,
            'prioridad'       => $mensaje->prioridad,
            'mensaje_padre_id'=> $mensaje->id,
        ]);

        return response()->json(['ok' => true]);
    }

    /**
     * Marcar un mensaje como leído.
     */
    public function leer(Mensaje $mensaje): JsonResponse
    {
        if ($mensaje->destinatario_id !== auth()->id()) {
            return response()->json(['ok' => false, 'error' => 'No autorizado'], 403);
        }

        if (!$mensaje->leido_en) {
            $mensaje->update(['leido_en' => now()]);
        }

        return response()->json(['ok' => true]);
    }
}