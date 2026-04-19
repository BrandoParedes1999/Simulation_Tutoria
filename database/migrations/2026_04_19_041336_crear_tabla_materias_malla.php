<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('materias_malla', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrera_id')
                  ->constrained('carreras')
                  ->cascadeOnDelete();
            $table->string('clave')->unique();
            $table->string('nombre');
            $table->integer('total_horas');
            $table->integer('creditos');
            $table->integer('semestre');
            $table->enum('nivel', ['basico', 'profesionalizante', 'terminal']);
            $table->enum('tipo', [
                'obligatoria', 'optativa', 'formativa', 'especial'
            ])->default('obligatoria');
            $table->enum('area', [
                'ciencias_sociales_humanidades',
                'matematicas_ciencias_basicas',
                'entorno_social',
                'arquitectura_computadoras',
                'redes',
                'software_base',
                'programacion',
                'tratamiento_informacion',
                'interaccion_humano_computadora',
                'optativos',
                'otras_ciencias',
            ]);
            $table->text('descripcion')->nullable();
            $table->boolean('activa')->default(true);
            $table->timestamps();

            $table->index(['carrera_id', 'semestre']);
            $table->index(['nivel', 'tipo']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('materias_malla');
    }
};