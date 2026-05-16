<?php

namespace App\Http\Controllers\Alumno;

use App\Http\Controllers\Controller;
use App\Models\Mensaje;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    public function marcarLeidas(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();
        return back();
    }

    public function abrirYLeer(Request $request, string $notifId): RedirectResponse
    {
        $notif = $request->user()->notifications()->findOrFail($notifId);
        $notif->markAsRead();

        $data = $notif->data;
        $tipo = $data['tipo'] ?? '';

        if ($tipo === 'mensaje_recibido') {
            // Compatibilidad con notificaciones antiguas que usaban mensaje_id
            $rawId = $data['conversacion_id'] ?? $data['mensaje_id'] ?? null;

            if ($rawId) {
                // Asegurar que siempre apuntamos al mensaje raíz
                $mensaje = Mensaje::find($rawId);
                $rootId  = $mensaje?->mensaje_padre_id ?? $mensaje?->id;

                $rol = $request->user()->rol;
                $ruta = $rol === 'tutor' ? 'tutor.mensajes' : 'alumno.mensajes';
                return redirect()->route($ruta, ['conversacion' => $rootId]);
            }
        }

        // Para asignacion_tutor u otros tipos → dashboard del alumno
        return redirect()->route('alumno.dashboard');
    }
}
