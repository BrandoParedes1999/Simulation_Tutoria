<?php

namespace App\Livewire\Auth;

use App\Mail\CodigoVerificacionRegistro;
use App\Models\Alumno;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.guest')]
class RegistroAlumno extends Component
{
    // ── Estado ───────────────────────────────────────────────────────────
    public int    $paso          = 1;
    public string $matricula     = '';
    public string $codigo        = '';
    public string $correoOculto  = '';
    public int    $intentos      = 0;
    public string $nombre        = '';
    public string $password      = '';
    public string $passwordConf  = '';
    public string $codigoDevMode = '';  // solo visible en APP_ENV=local
    public string $errorEnvio    = '';  // error detallado de SMTP

    const DOMINIO = '@mail.unacar.mx';

    // ════════════════════════════════════════════════════════════════════
    //  PASO 1 — Enviar código al correo institucional
    // ════════════════════════════════════════════════════════════════════

    public function enviarCodigo(): void
    {
        $this->codigoDevMode = '';
        $this->errorEnvio    = '';

        $this->validate(
            ['matricula' => 'required|string|min:4|max:15'],
            ['matricula.required' => 'Ingresa tu número de matrícula.']
        );

        $mat    = strtoupper(trim($this->matricula));
        $correo = strtolower($mat) . self::DOMINIO;

        // ── Verificar que no tenga cuenta ya ──────────────────────────
        $yaRegistrado = User::where('email', $correo)->exists()
            || Alumno::where('matricula', $mat)->whereNotNull('usuario_id')->exists();

        if ($yaRegistrado) {
            $this->addError('matricula', 'Esta matrícula ya tiene cuenta activa. Usa "Iniciar sesión".');
            return;
        }

        // ── Generar código y guardarlo en caché ───────────────────────
        $codigo = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put("reg_{$mat}", [
            'codigo'    => $codigo,
            'matricula' => $mat,
            'correo'    => $correo,
        ], now()->addMinutes(10));

        // ── Intentar enviar el correo ──────────────────────────────────
        try {
            Mail::to($correo)->send(new CodigoVerificacionRegistro($codigo, $mat));

        } catch (\Throwable $e) {
            Cache::forget("reg_{$mat}");

            // Guardar el mensaje técnico para mostrarlo en local
            $this->errorEnvio = $e->getMessage();

            Log::error("Registro [{$mat}] – fallo SMTP: {$e->getMessage()}");

            $this->addError(
                'matricula',
                app()->isLocal()
                    ? "Error SMTP: {$e->getMessage()}"
                    : 'No se pudo enviar el correo. Intenta más tarde o contacta soporte.'
            );
            return;
        }

        // ── En desarrollo: mostrar el código en pantalla ───────────────
        if (app()->isLocal()) {
            $this->codigoDevMode = $codigo;
            Log::info(
                "\n" . str_repeat('═', 55) .
                "\n  🔑 CÓDIGO  [{$mat}]  →  {$correo}" .
                "\n  CÓDIGO: {$codigo}" .
                "\n" . str_repeat('═', 55)
            );
        }

        // ── Enmascarar correo para la vista ───────────────────────────
        // Ejemplo: 190*** @mail.unacar.mx
        $vis = min(3, strlen(strtolower($mat)));
        $this->correoOculto = substr(strtolower($mat), 0, $vis)
            . str_repeat('*', max(2, strlen($mat) - $vis))
            . self::DOMINIO;

        $this->matricula = $mat;
        $this->intentos  = 0;
        $this->paso      = 2;
    }

    // ════════════════════════════════════════════════════════════════════
    //  PASO 2 — Verificar código
    // ════════════════════════════════════════════════════════════════════

    public function verificarCodigo(): void
    {
        $this->validate(
            ['codigo' => 'required|digits:6'],
            [
                'codigo.required' => 'Ingresa el código de 6 dígitos.',
                'codigo.digits'   => 'Solo 6 dígitos numéricos.',
            ]
        );

        // Bloquear tras 3 intentos
        if ($this->intentos >= 3) {
            Cache::forget("reg_{$this->matricula}");
            $this->reset(['codigo', 'intentos', 'correoOculto', 'codigoDevMode']);
            $this->paso = 1;
            $this->dispatch('toast', tipo: 'error', mensaje: 'Demasiados intentos. Solicita un nuevo código.');
            return;
        }

        $datos = Cache::get("reg_{$this->matricula}");

        if (! $datos) {
            $this->addError('codigo', 'El código expiró (10 min). Regresa al paso 1.');
            return;
        }

        if ($datos['codigo'] !== $this->codigo) {
            $this->intentos++;
            $rest = 3 - $this->intentos;
            $this->addError('codigo', "Código incorrecto. Te quedan {$rest} intento(s).");
            return;
        }

        $this->codigoDevMode = '';
        $this->paso = 3;
    }

    // ════════════════════════════════════════════════════════════════════
    //  PASO 2 — Reenviar código
    // ════════════════════════════════════════════════════════════════════

    public function reenviarCodigo(): void
    {
        Cache::forget("reg_{$this->matricula}");
        $this->reset(['codigo', 'correoOculto', 'intentos', 'codigoDevMode']);
        $this->paso = 1;
        $this->dispatch('toast', tipo: 'info', mensaje: 'Ingresa tu matrícula para recibir un nuevo código.');
    }

    // ════════════════════════════════════════════════════════════════════
    //  PASO 3 — Crear cuenta
    // ════════════════════════════════════════════════════════════════════

    public function completar(): void
    {
        $this->validate([
            'nombre'       => 'required|string|min:3|max:255',
            'password'     => 'required|min:8',
            'passwordConf' => 'required|same:password',
        ], [
            'nombre.required'       => 'El nombre completo es obligatorio.',
            'nombre.min'            => 'Mínimo 3 caracteres.',
            'password.required'     => 'La contraseña es obligatoria.',
            'password.min'          => 'Mínimo 8 caracteres.',
            'passwordConf.required' => 'Confirma tu contraseña.',
            'passwordConf.same'     => 'Las contraseñas no coinciden.',
        ]);

        $datos = Cache::get("reg_{$this->matricula}");

        if (! $datos) {
            $this->reset();
            $this->paso = 1;
            $this->dispatch('toast', tipo: 'error', mensaje: 'Sesión expirada. Empieza de nuevo.');
            return;
        }

        if (User::where('email', $datos['correo'])->exists()) {
            $this->reset();
            $this->paso = 1;
            $this->dispatch('toast', tipo: 'error', mensaje: 'Esta matrícula ya tiene cuenta. Inicia sesión.');
            return;
        }

        try {
            DB::transaction(function () use ($datos) {

                // Crear el usuario
                $user = User::create([
                    'name'     => trim($this->nombre),
                    'email'    => $datos['correo'],
                    'password' => Hash::make($this->password),
                    'rol'      => 'alumno',
                    'activo'   => true,
                ]);

                // Buscar si ya existe un registro de alumno en BD (pre-cargado por admin)
                $alumno = Alumno::where('matricula', $datos['matricula'])
                    ->whereNull('usuario_id')
                    ->first();

                if ($alumno) {
                    // Ya existe — solo vincular el usuario
                    $alumno->update([
                        'usuario_id'           => $user->id,
                        'correo_institucional' => $datos['correo'],
                    ]);
                } else {
                    // No existe — crear registro básico
                    // El tutor/admin puede completar semestre, carrera, etc.
                    Alumno::create([
                        'usuario_id'           => $user->id,
                        'matricula'            => $datos['matricula'],
                        'correo_institucional' => $datos['correo'],
                        'carrera_id'           => 1,      // ISC por defecto
                        'semestre_actual'      => 1,
                        'fecha_ingreso'        => now(),
                        'estatus'              => 'activo',
                        'creditos_aprobados'   => 0,
                    ]);
                }

                Cache::forget("reg_{$datos['matricula']}");

                Auth::login($user);
                session()->regenerate();
            });

        } catch (\Throwable $e) {
            Log::error("Registro [{$this->matricula}] – error al crear cuenta: {$e->getMessage()}");
            $this->dispatch('toast', tipo: 'error', mensaje: 'Error al crear la cuenta. Intenta de nuevo.');
            return;
        }

        $this->redirect(route('alumno.dashboard'), navigate: true);
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.auth.registro-alumno');
    }
}