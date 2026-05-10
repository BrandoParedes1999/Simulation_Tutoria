<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Anuncio extends Model
{
    protected $table    = 'anuncios';
    protected $fillable = [
        'titulo','contenido','imagen_url','categoria',
        'enlace','fecha_expiracion','activo','destacado','orden',
    ];
    protected $casts = [
        'activo'           => 'boolean',
        'destacado'        => 'boolean',
        'fecha_expiracion' => 'date',
    ];

    public function scopeActivos($query)
    {
        return $query->where('activo', true)
            ->where(fn($q) =>
                $q->whereNull('fecha_expiracion')
                  ->orWhere('fecha_expiracion', '>=', now()->toDateString())
            )
            ->orderByDesc('destacado')
            ->orderBy('orden')
            ->orderByDesc('created_at');
    }

    public function colorCategoria(): array
    {
        return match($this->categoria) {
            'urgente'   => ['bg'=>'bg-red-100',    'text'=>'text-red-700',    'dot'=>'bg-red-500',    'border'=>'border-red-200',    'label'=>'Urgente'],
            'academico' => ['bg'=>'bg-blue-100',   'text'=>'text-blue-700',   'dot'=>'bg-blue-500',   'border'=>'border-blue-200',   'label'=>'Académico'],
            'evento'    => ['bg'=>'bg-violet-100', 'text'=>'text-violet-700', 'dot'=>'bg-violet-500', 'border'=>'border-violet-200', 'label'=>'Evento'],
            'beca'      => ['bg'=>'bg-emerald-100','text'=>'text-emerald-700','dot'=>'bg-emerald-500','border'=>'border-emerald-200','label'=>'Beca'],
            default     => ['bg'=>'bg-amber-100',  'text'=>'text-amber-700',  'dot'=>'bg-amber-500',  'border'=>'border-amber-200',  'label'=>'Aviso'],
        };
    }
}