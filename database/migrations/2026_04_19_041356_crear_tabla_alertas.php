<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('alertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')
                  ->constrained('alumnos')
                  ->cascadeOnDelete();

            $table->enum('prioridad', ['critica', 'media', 'baja']);
            $table->enum('categoria', [
                'calificacion_baja',
                'promedio_semestral_bajo',
                'materia_reprobada',
                'caida_calificacion',
                'otra'
            ]);

            $table->string('titulo');
            $table->text('mensaje');

            $table->foreignId('materia_relacionada_id')
                  ->nullable()
                  ->constrained('materias_malla')
                  ->nullOnDelete();
            $table->foreignId('inscripcion_relacionada_id')
                  ->nullable()
                  ->constrained('inscripciones')
                  ->nullOnDelete();

            $table->boolean('atendida')->default(false);
            $table->foreignId('atendida_por')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamp('atendida_en')->nullable();
            $table->text('nota_atencion')->nullable();

            $table->timestamps();

            $table->index(['alumno_id', 'atendida']);
            $table->index(['prioridad', 'atendida']);
            $table->index(['categoria', 'created_at']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('alertas');
    }
};