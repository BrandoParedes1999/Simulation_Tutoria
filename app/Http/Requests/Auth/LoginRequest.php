<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\Alumno;
use App\Models\Tutor;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'identificador' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Intenta autenticar con: email, matrícula (alumno) o número de empleado (tutor)
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $identificador = $this->input('identificador');
        $user = null;

        // 1. Intentar por email
        if (filter_var($identificador, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', $identificador)->first();
        } else {
            // 2. Intentar por matrícula (alumno)
            $alumno = Alumno::where('matricula', $identificador)->first();
            if ($alumno) {
                $user = $alumno->usuario;
            }
            
            // 3. Intentar por número de empleado (tutor)
            if (!$user) {
                $tutor = Tutor::where('numero_empleado', $identificador)->first();
                if ($tutor) {
                    $user = $tutor->usuario;
                }
            }
        }

        if (!$user || !Auth::attempt(
            ['email' => $user->email, 'password' => $this->input('password')],
            $this->boolean('remember')
        )) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'identificador' => 'Las credenciales no son correctas.',
            ]);
        }

        // Verificar que el usuario esté activo
        if (!Auth::user()->activo) {
            Auth::logout();
            throw ValidationException::withMessages([
                'identificador' => 'Esta cuenta ha sido deshabilitada.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'identificador' => "Demasiados intentos. Intenta de nuevo en $seconds segundos.",
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(
            Str::lower($this->input('identificador')).'|'.$this->ip()
        );
    }
}