<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('periodos', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique();
            $table->string('nombre');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->date('fecha_limite_inscripcion');
            $table->date('fecha_limite_baja')->nullable();
            $table->boolean('es_actual')->default(false);
            $table->timestamps();

            $table->index('es_actual');
        });
    }

    public function down(): void {
        Schema::dropIfExists('periodos');
    }
};