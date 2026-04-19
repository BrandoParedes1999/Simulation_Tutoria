<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('mensajes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('remitente_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->foreignId('destinatario_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->enum('tipo_destinatario', ['individual', 'grupal'])
                  ->default('individual');

            $table->string('asunto');
            $table->text('contenido');

            $table->enum('prioridad', ['urgente', 'normal', 'informativo'])
                  ->default('normal');

            $table->string('plantilla_usada')->nullable();
            $table->boolean('importante')->default(false);
            $table->timestamp('leido_en')->nullable();

            $table->foreignId('mensaje_padre_id')
                  ->nullable()
                  ->constrained('mensajes')
                  ->nullOnDelete();

            $table->timestamps();

            $table->index(['destinatario_id', 'leido_en']);
            $table->index(['remitente_id', 'created_at']);
            $table->index('mensaje_padre_id');
        });
    }

    public function down(): void {
        Schema::dropIfExists('mensajes');
    }
};