<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('alumnos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->string('matricula', 15)->unique();
            $table->foreignId('carrera_id')
                  ->constrained('carreras');
            $table->integer('semestre_actual')->default(1);
            $table->date('fecha_ingreso')->nullable();
            $table->foreignId('tutor_id')
                  ->nullable()
                  ->constrained('tutores')
                  ->nullOnDelete();
            $table->enum('estatus', [
                'activo', 'baja_temporal', 'egresado', 'baja_definitiva'
            ])->default('activo');
            $table->integer('creditos_aprobados')->default(0);
            $table->decimal('promedio_general', 5, 2)->nullable();
            $table->timestamps();

            $table->index(['tutor_id', 'semestre_actual']);
            $table->index('estatus');
            $table->index('matricula');
        });
    }

    public function down(): void {
        Schema::dropIfExists('alumnos');
    }
};