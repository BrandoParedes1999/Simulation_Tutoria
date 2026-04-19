<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mensaje extends Model {
    protected $table = 'mensajes';

    protected $fillable = [
        'remitente_id', 'destinatario_id', 'tipo_destinatario',
        'asunto', 'contenido', 'prioridad', 'plantilla_usada',
        'importante', 'leido_en', 'mensaje_padre_id',
    ];

    protected $casts = [
        'leido_en' => 'datetime',
        'importante' => 'boolean',
    ];

    public function remitente() { return $this->belongsTo(User::class, 'remitente_id'); }
    public function destinatario() { return $this->belongsTo(User::class, 'destinatario_id'); }
    public function mensajePadre() { return $this->belongsTo(Mensaje::class, 'mensaje_padre_id'); }
    public function respuestas() { return $this->hasMany(Mensaje::class, 'mensaje_padre_id'); }

    public function estaLeido(): bool { return $this->leido_en !== null; }
}