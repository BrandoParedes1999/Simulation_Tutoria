<?php

namespace App\Http\Controllers\Alumno;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    public function marcarLeidas(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();
        return back();
    }
}
