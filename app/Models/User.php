<?php
namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable {
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'rol', 'foto', 'telefono', 'activo',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'activo' => 'boolean',
    ];

    // Relaciones
    public function alumno() { return $this->hasOne(Alumno::class, 'usuario_id'); }
    public function tutor() { return $this->hasOne(Tutor::class, 'usuario_id'); }
    public function mensajesEnviados() { return $this->hasMany(Mensaje::class, 'remitente_id'); }
    public function mensajesRecibidos() { return $this->hasMany(Mensaje::class, 'destinatario_id'); }

    // Helpers de rol
    public function esAlumno(): bool { return $this->rol === 'alumno'; }
    public function esTutor(): bool { return $this->rol === 'tutor'; }
    public function esAdmin(): bool { return $this->rol === 'admin'; }
}