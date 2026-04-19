<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inscripciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')
                  ->constrained('alumnos')
                  ->cascadeOnDelete();
            $table->foreignId('materia_malla_id')
                  ->constrained('materias_malla');
            $table->foreignId('periodo_id')
                  ->constrained('periodos');

            $table->string('profesor')->nullable();
            $table->string('horario')->nullable();
            $table->string('aula')->nullable();
            $table->string('grupo')->nullable();

            // Calificaciones en escala 0-100 (capturadas por el alumno)
            $table->decimal('parcial1', 5, 2)->nullable();
            $table->decimal('parcial2', 5, 2)->nullable();
            $table->decimal('parcial3', 5, 2)->nullable();
            $table->decimal('calificacion_final', 5, 2)->nullable();
            $table->decimal('promedio', 5, 2)->nullable();

            $table->enum('estatus', [
                'en_curso', 'aprobada', 'reprobada', 'dada_de_baja'
            ])->default('en_curso');

            $table->timestamps();

            $table->unique(['alumno_id', 'materia_malla_id', 'periodo_id'], 'unica_inscripcion');
            $table->index(['alumno_id', 'estatus']);
            $table->index(['periodo_id', 'estatus']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('inscripciones');
    }
};