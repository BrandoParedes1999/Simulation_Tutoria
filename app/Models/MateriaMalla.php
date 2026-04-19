<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MateriaMalla extends Model {
    protected $table = 'materias_malla';

    protected $fillable = [
        'carrera_id', 'clave', 'nombre', 'total_horas', 'creditos',
        'semestre', 'nivel', 'tipo', 'area', 'descripcion', 'activa',
    ];

    protected $casts = ['activa' => 'boolean'];

    public function carrera() { return $this->belongsTo(Carrera::class); }

    public function prerrequisitos() {
        return $this->belongsToMany(
            MateriaMalla::class,
            'prerrequisitos',
            'materia_id',
            'prerrequisito_id'
        );
    }

    public function inscripciones() { return $this->hasMany(Inscripcion::class); }
}