<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alerta extends Model {
    protected $table = 'alertas';

    protected $fillable = [
        'alumno_id', 'prioridad', 'categoria', 'titulo', 'mensaje',
        'materia_relacionada_id', 'inscripcion_relacionada_id',
        'atendida', 'atendida_por', 'atendida_en', 'nota_atencion',
    ];

    protected $casts = [
        'atendida' => 'boolean',
        'atendida_en' => 'datetime',
    ];

    public function alumno() { return $this->belongsTo(Alumno::class); }
    public function materiaRelacionada() { return $this->belongsTo(MateriaMalla::class, 'materia_relacionada_id'); }
    public function inscripcionRelacionada() { return $this->belongsTo(Inscripcion::class, 'inscripcion_relacionada_id'); }
    public function atendidaPor() { return $this->belongsTo(User::class, 'atendida_por'); }

    public function scopeActivas($query) { return $query->where('atendida', false); }
    public function scopeCriticas($query) { return $query->where('prioridad', 'critica'); }
}