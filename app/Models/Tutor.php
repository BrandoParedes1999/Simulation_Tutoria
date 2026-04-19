<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tutor extends Model {
    protected $table = 'tutores';

    protected $fillable = [
        'usuario_id', 'numero_empleado', 'departamento', 'cubiculo', 'grado_academico',
    ];

    public function usuario() { return $this->belongsTo(User::class, 'usuario_id'); }
    public function alumnosAsignados() { return $this->hasMany(Alumno::class, 'tutor_id'); }
    public function reglasAlerta() { return $this->hasMany(ReglaAlerta::class, 'tutor_id'); }
}