<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carrera extends Model {
    protected $table = 'carreras';

    protected $fillable = [
        'nombre', 'clave', 'total_semestres', 'total_creditos',
        'total_horas', 'horas_servicio_social', 'horas_practicas_profesionales',
        'plan', 'descripcion', 'activa',
    ];

    protected $casts = ['activa' => 'boolean'];

    public function materiasMalla() { return $this->hasMany(MateriaMalla::class); }
    public function alumnos() { return $this->hasMany(Alumno::class); }
}