<?php

namespace App\Http\Controllers\Alumno;

use App\Http\Controllers\Controller;
use App\Models\Mensaje;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MensajeController extends Controller
{
    /**
     * Responder un mensaje (alumno → tutor).
     */
    public function responder(Request $request, Mensaje $mensaje): JsonResponse
    {
        $request->validate(['contenido' => 'required|string|max:5000']);

        $userId = auth()->id();

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