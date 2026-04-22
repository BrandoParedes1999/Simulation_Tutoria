<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Periodo;
use App\Models\Inscripcion;
use App\Models\Alerta;
use App\Models\Mensaje;

class TutorController extends Controller
{
    // ══════════════════════════════════════
    //  DASHBOARD
    // ══════════════════════════════════════
    public function dashboard()
    {
        $tutor = auth()->user()->tutor;

        if (!$tutor) {
            return redirect()->back()->with('error', 'No tienes tutor asignado');
        }

        $alumnos = $tutor->alumnosAsignados;
        $ids     = $alumnos->pluck('id');

        $periodoActual = Periodo::where('es_actual', '!=', 0)
            ->orderByDesc('fecha_inicio')
            ->first();

        $reglas = $tutor->reglasAlerta->where('activa', true);

        // ── Generar alertas automáticamente ──
        foreach ($alumnos as $alumno) {
            foreach ($reglas as $regla) {

                if ($regla->clave_regla === 'promedio_semestral_minimo') {
                    $umbral = $regla->umbral >= 10
                        ? $regla->umbral / 10
                        : $regla->umbral;
                    $prom = (float)$alumno->promedio_general;

                    if ($prom > 0 && $prom < $umbral) {
                        Alerta::firstOrCreate(
                            [
                                'alumno_id' => $alumno->id,
                                'categoria' => 'caida_calificacion',
                                'atendida'  => false,
                            ],
                            [
                                'titulo'    => 'Promedio bajo ' . number_format($prom, 1),
                                'prioridad' => $regla->prioridad_alerta,
                                'mensaje'   => "Promedio {$prom} por debajo del mínimo ({$umbral}).",
                            ]
                        );
                    }
                }

                if ($regla->clave_regla === 'calificacion_minima_materia' && $periodoActual) {
                    $umbral = $regla->umbral >= 10
                        ? $regla->umbral / 10
                        : $regla->umbral;

                    Inscripcion::where('alumno_id', $alumno->id)
                        ->where('periodo_id', $periodoActual->id)
                        ->whereNotNull('calificacion_final')
                        ->where('calificacion_final', '>', 0)
                        ->where('calificacion_final', '<', $umbral)
                        ->with('materiaMalla')
                        ->get()
                        ->each(function ($insc) use ($alumno, $regla, $umbral) {
                            $nombre = $insc->materiaMalla->nombre ?? 'materia';
                            Alerta::firstOrCreate(
                                [
                                    'alumno_id'                  => $alumno->id,
                                    'inscripcion_relacionada_id' => $insc->id,
                                    'categoria'                  => 'caida_calificacion',
                                    'atendida'                   => false,
                                ],
                                [
                                    'titulo'    => "Calificación baja en {$nombre}",
                                    'prioridad' => $regla->prioridad_alerta,
                                    'mensaje'   => "Calificación {$insc->calificacion_final} menor a {$umbral}.",
                                ]
                            );
                        });
                }

                if ($regla->clave_regla === 'caida_calificacion_puntos' && $periodoActual) {
                    $umbralCaida = $regla->umbral >= 10
                        ? $regla->umbral / 10
                        : $regla->umbral;

                    Inscripcion::where('alumno_id', $alumno->id)
                        ->where('periodo_id', $periodoActual->id)
                        ->whereNotNull('parcial1')
                        ->whereNotNull('parcial2')
                        ->with('materiaMalla')
                        ->get()
                        ->each(function ($insc) use ($alumno, $regla, $umbralCaida) {
                            $caida = (float)$insc->parcial1 - (float)$insc->parcial2;
                            if ($caida >= $umbralCaida) {
                                $nombre = $insc->materiaMalla->nombre ?? 'materia';
                                Alerta::firstOrCreate(
                                    [
                                        'alumno_id'                  => $alumno->id,
                                        'inscripcion_relacionada_id' => $insc->id,
                                        'categoria'                  => 'caida_calificacion',
                                        'atendida'                   => false,
                                    ],
                                    [
                                        'titulo'    => "Caída de " . number_format($caida, 1) . " pts en {$nombre}",
                                        'prioridad' => $regla->prioridad_alerta,
                                        'mensaje'   => "Caída de {$caida} puntos entre parcial 1 y parcial 2.",
                                    ]
                                );
                            }
                        });
                }
            }
        }

        return view('tutor.dashboard', compact('tutor', 'alumnos', 'ids', 'periodoActual'));
    }

    // ══════════════════════════════════════
    //  MENSAJES
    // ══════════════════════════════════════
   public function enviarMensaje(Request $request)
{
    try {
        $tutor   = auth()->user()->tutor;
        $alumnos = $tutor->alumnosAsignados;

        $request->validate([
            'tipo'      => 'required|in:alumno,grupo',
            'asunto'    => 'required|string|max:191',
            'contenido' => 'required|string',
            'prioridad' => 'required|in:urgente,normal,informativo',
        ]);

        if ($request->tipo === 'grupo') {
            foreach ($alumnos as $alumno) {
                Mensaje::create([
                    'remitente_id'      => auth()->id(),
                    'destinatario_id'   => $alumno->usuario_id,
                    'tipo_destinatario' => 'grupal',      // ← cambiado
                    'asunto'            => $request->asunto,
                    'contenido'         => $request->contenido,
                    'prioridad'         => $request->prioridad,
                    'plantilla_usada'   => $request->plantilla ?? null,
                    'importante'        => false,
                ]);
            }
        } else {
            $request->validate([
                'destinatario_id' => 'required|integer',
            ]);

            $alumno = $alumnos->firstWhere('usuario_id', $request->destinatario_id);

            if (!$alumno) {
                return response()->json([
                    'ok'    => false,
                    'error' => 'Alumno no autorizado',
                ], 403);
            }

            Mensaje::create([
                'remitente_id'      => auth()->id(),
                'destinatario_id'   => $request->destinatario_id,
                'tipo_destinatario' => 'individual',      // ← cambiado
                'asunto'            => $request->asunto,
                'contenido'         => $request->contenido,
                'prioridad'         => $request->prioridad,
                'plantilla_usada'   => $request->plantilla ?? null,
                'importante'        => false,
            ]);
        }

        return response()->json(['ok' => true]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'ok'     => false,
            'errores'=> $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'ok'    => false,
            'error' => $e->getMessage(),
            'linea' => $e->getLine(),
        ], 500);
    }
}

public function responderMensaje(Request $request, $id)
{
    $request->validate([
        'contenido' => 'required|string',
    ]);

    $mensajePadre = Mensaje::findOrFail($id);

    Mensaje::create([
        'remitente_id'      => auth()->id(),
        'destinatario_id'   => $mensajePadre->remitente_id,
        'tipo_destinatario' => 'individual',              // ← cambiado
        'asunto'            => 'Re: ' . $mensajePadre->asunto,
        'contenido'         => $request->contenido,
        'prioridad'         => 'normal',
        'mensaje_padre_id'  => $mensajePadre->mensaje_padre_id ?? $mensajePadre->id,
        'importante'        => false,
    ]);

    return response()->json(['ok' => true]);
}
    public function marcarLeido($id)
    {
        $mensaje = Mensaje::findOrFail($id);

        if ($mensaje->destinatario_id === auth()->id()) {
            $mensaje->update(['leido_en' => now()]);
        }

        return response()->json(['ok' => true]);
    }

    // ══════════════════════════════════════
    //  ALERTAS
    // ══════════════════════════════════════
    public function marcarAlertaAtendida($id)
    {
        $tutor  = auth()->user()->tutor;
        $ids    = $tutor->alumnosAsignados->pluck('id');

        $alerta = Alerta::whereIn('alumno_id', $ids)->findOrFail($id);

        $alerta->update([
            'atendida'     => true,
            'atendida_en'  => now(),
            'atendida_por' => auth()->id(),
        ]);

        return response()->json(['ok' => true]);
    }
    


    
}