<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Periodo extends Model {
    protected $table = 'periodos';

    protected $fillable = [
        'clave', 'nombre', 'fecha_inicio', 'fecha_fin',
        'fecha_limite_inscripcion', 'fecha_limite_baja', 'es_actual',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_limite_inscripcion' => 'date',
        'fecha_limite_baja' => 'date',
        'es_actual' => 'boolean',
    ];

    public function inscripciones() { return $this->hasMany(Inscripcion::class); }

    public function estaAbiertoParaInscripcion(): bool {
        return now()->lte($this->fecha_limite_inscripcion);
    }

    public function estaAbiertoParaBaja(): bool {
        return $this->fecha_limite_baja && now()->lte($this->fecha_limite_baja);
    }
}