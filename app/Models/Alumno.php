<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alumno extends Model {
    // Constantes del sistema
    const CALIFICACION_APROBATORIA = 70;
    const PROMEDIO_EXCELENTE = 90;
    const PROMEDIO_REGULAR_MIN = 70;
    const MAX_CREDITOS_POR_SEMESTRE = 40;

    protected $table = 'alumnos';

    protected $fillable = [
        'usuario_id', 'matricula', 'carrera_id', 'semestre_actual',
        'fecha_ingreso', 'tutor_id', 'estatus',
        'creditos_aprobados', 'promedio_general',
    ];

    protected $casts = [
        'fecha_ingreso' => 'date',
        'promedio_general' => 'float',
    ];

    // Relaciones
    public function usuario() { return $this->belongsTo(User::class, 'usuario_id'); }
    public function carrera() { return $this->belongsTo(Carrera::class); }
    public function tutor() { return $this->belongsTo(Tutor::class); }
    public function inscripciones() { return $this->hasMany(Inscripcion::class); }
    public function alertas() { return $this->hasMany(Alerta::class); }

    public function inscripcionesActuales() {
        return $this->inscripciones()
            ->whereHas('periodo', fn($q) => $q->where('es_actual', true))
            ->with('materiaMalla');
    }

    // Métodos de cálculo
    public function calcularCreditosAprobados(): int {
        return $this->inscripciones()
            ->where('estatus', 'aprobada')
            ->with('materiaMalla')
            ->get()
            ->sum(fn($i) => $i->materiaMalla->creditos);
    }

    public function calcularCreditosEnCurso(): int {
        return $this->inscripciones()
            ->where('estatus', 'en_curso')
            ->with('materiaMalla')
            ->get()
            ->sum(fn($i) => $i->materiaMalla->creditos);
    }

    public function calcularPromedioGeneral(): float {
        $aprobadas = $this->inscripciones()
            ->where('estatus', 'aprobada')
            ->whereNotNull('promedio')
            ->get();

        return $aprobadas->isEmpty() ? 0 : round($aprobadas->avg('promedio'), 2);
    }

    // Clasificación dinámica
    public function getClasificacionAttribute(): string {
        $promedio = $this->promedio_general ?? $this->calcularPromedioGeneral();
        if ($promedio >= self::PROMEDIO_EXCELENTE) return 'excelente';
        if ($promedio >= self::PROMEDIO_REGULAR_MIN) return 'regular';
        return 'en_riesgo';
    }

    public function getAlertasActivasCountAttribute(): int {
        return $this->alertas()->where('atendida', false)->count();
    }

    // Scopes
    public function scopeEnRiesgo($query) {
        return $query->where('promedio_general', '<', self::PROMEDIO_REGULAR_MIN);
    }

    public function scopeExcelentes($query) {
        return $query->where('promedio_general', '>=', self::PROMEDIO_EXCELENTE);
    }
}