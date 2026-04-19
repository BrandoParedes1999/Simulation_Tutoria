<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReglaAlerta extends Model {
    protected $table = 'reglas_alerta';

    protected $fillable = [
        'tutor_id', 'clave_regla', 'descripcion', 'umbral',
        'prioridad_alerta', 'activa',
    ];

    protected $casts = [
        'umbral' => 'float',
        'activa' => 'boolean',
    ];

    public function tutor() { return $this->belongsTo(Tutor::class); }
}