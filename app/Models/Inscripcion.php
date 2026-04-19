<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inscripcion extends Model {
    const CALIFICACION_APROBATORIA = 70;
    const CALIFICACION_MAXIMA = 100;

    protected $table = 'inscripciones';

    protected $fillable = [
        'alumno_id', 'materia_malla_id', 'periodo_id',
        'profesor', 'horario', 'aula', 'grupo',
        'parcial1', 'parcial2', 'parcial3', 'calificacion_final',
        'promedio', 'estatus',
    ];

    protected $casts = [
        'parcial1' => 'float',
        'parcial2' => 'float',
        'parcial3' => 'float',
        'calificacion_final' => 'float',
        'promedio' => 'float',
    ];

    protected static function booted() {
        static::saving(function($inscripcion) {
            // Calcular promedio con calificaciones disponibles
            $calificaciones = array_filter([
                $inscripcion->parcial1,
                $inscripcion->parcial2,
                $inscripcion->parcial3,
                $inscripcion->calificacion_final,
            ], fn($c) => $c !== null && $c >= 0);

            if (count($calificaciones) > 0) {
                $inscripcion->promedio = round(
                    array_sum($calificaciones) / count($calificaciones), 2
                );

                // Auto-asignar estatus cuando hay calificación final
                if ($inscripcion->calificacion_final !== null
                    && $inscripcion->estatus === 'en_curso') {
                    $inscripcion->estatus = $inscripcion->promedio >= self::CALIFICACION_APROBATORIA
                        ? 'aprobada'
                        : 'reprobada';
                }
            }
        });
    }

    public function alumno() { return $this->belongsTo(Alumno::class); }
    public function materiaMalla() { return $this->belongsTo(MateriaMalla::class, 'materia_malla_id'); }
    public function periodo() { return $this->belongsTo(Periodo::class); }

    public function estaAprobada(): bool { return $this->estatus === 'aprobada'; }
    public function estaReprobada(): bool { return $this->estatus === 'reprobada'; }
}