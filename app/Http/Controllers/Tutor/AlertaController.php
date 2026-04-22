<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Alerta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlertaController extends Controller
{
    /**
     * Marcar una alerta como atendida.
     * Antes, el botón solo actualizaba el estado en Alpine.js (local).
     * Ahora persiste en la base de datos.
     */
    public function atender(Request $request, Alerta $alerta): JsonResponse
    {
        $tutor = auth()->user()->tutor;

        // Verificar que la alerta pertenece a un alumno asignado a este tutor
        $alumnosIds = $tutor->alumnosAsignados()->pluck('id');

        if (!$alumnosIds->contains($alerta->alumno_id)) {
            return response()->json(['ok' => false, 'error' => 'No autorizado'], 403);
        }

        if ($alerta->atendida) {
            return response()->json(['ok' => true, 'mensaje' => 'Ya estaba atendida']);
        }

        $alerta->update([
            'atendida'    => true,
            'atendida_por'=> auth()->id(),
            'atendida_en' => now(),
            'nota_atencion'=> $request->nota,
        ]);

        return response()->json(['ok' => true]);
    }
}