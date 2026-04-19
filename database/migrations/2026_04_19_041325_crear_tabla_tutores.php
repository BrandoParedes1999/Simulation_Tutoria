<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tutores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->string('numero_empleado')->unique();
            $table->string('departamento')->nullable();
            $table->string('cubiculo')->nullable();
            $table->string('grado_academico')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('tutores');
    }
};