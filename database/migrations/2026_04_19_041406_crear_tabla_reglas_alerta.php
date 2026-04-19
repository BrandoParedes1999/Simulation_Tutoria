<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('reglas_alerta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tutor_id')
                  ->constrained('tutores')
                  ->cascadeOnDelete();

            $table->string('clave_regla');
            $table->string('descripcion');
            $table->decimal('umbral', 6, 2);
            $table->enum('prioridad_alerta', ['critica', 'media', 'baja'])
                  ->default('media');

            $table->boolean('activa')->default(true);
            $table->timestamps();

            $table->unique(['tutor_id', 'clave_regla']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('reglas_alerta');
    }
};