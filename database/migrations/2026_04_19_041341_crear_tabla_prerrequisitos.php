<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('prerrequisitos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('materia_id')
                  ->constrained('materias_malla')
                  ->cascadeOnDelete();
            $table->foreignId('prerrequisito_id')
                  ->constrained('materias_malla')
                  ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['materia_id', 'prerrequisito_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('prerrequisitos');
    }
};