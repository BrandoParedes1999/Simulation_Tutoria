<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Alerta;
use App\Models\ReglaAlerta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlertaController extends Controller
{
    /**
     * Marcar una alerta como atendida.
     */
    public function atender(Request $request, Alerta $alerta): JsonResponse
    {
        $tutor = auth()->user()->tutor;

        $alumnosIds = $tutor->alumnosAsignados()->pluck('id');

        if (!$alumnosIds->contains($alerta->alumno_id)) {
            return response()->json(['ok' => false, 'error' => 'No autorizado'], 403);
        }

        if ($alerta->atendida) {
            return response()->json(['ok' => true, 'mensaje' => 'Ya estaba atendida']);
        }

        $alerta->update([
            'atendida'      => true,
            'atendida_por'  => auth()->id(),
            'atendida_en'   => now(),
            'nota_atencion' => $request->nota,
        ]);

        return response()->json(['ok' => true]);
    }

    /**
     * Guardar configuración de reglas de alerta (umbral + activa).
     * Llamado desde el panel "Configurar Reglas" vía fetch/Alpine.js.
     */
    public function guardarReglas(Request $request): JsonResponse
    {
        $request->validate([
            'reglas'          => 'required|array',
            'reglas.*.id'     => 'required|integer|exists:reglas_alerta,id',
            'reglas.*.activa' => 'required|boolean',
            'reglas.*.umbral' => 'required|numeric|min:0|max:100',
        ]);

        $tutor = auth()->user()->tutor;

        foreach ($request->reglas as $dato) {
            // Verificación de seguridad: solo actualiza reglas propias del tutor
            ReglaAlerta::where('id', $dato['id'])
                ->where('tutor_id', $tutor->id)
                ->update([
                    'activa' => (bool) $dato['activa'],
                    'umbral' => (float) $dato['umbral'],
                ]);
        }

        return response()->json(['ok' => true]);
    }
}