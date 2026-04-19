<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('carreras', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('clave')->unique();
            $table->integer('total_semestres')->default(9);
            $table->integer('total_creditos')->default(320);
            $table->integer('total_horas')->default(4864);
            $table->integer('horas_servicio_social')->default(480);
            $table->integer('horas_practicas_profesionales')->default(320);
            $table->string('plan')->default('2010');
            $table->text('descripcion')->nullable();
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('carreras');
    }
};