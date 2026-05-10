<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            // Permitir alumnos pre-cargados sin cuenta de usuario todavía
            $table->unsignedBigInteger('usuario_id')->nullable()->change();

            // Correo institucional para enviar el código de verificación
            $table->string('correo_institucional', 100)->nullable()->after('matricula');
        });
    }

    public function down(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            $table->unsignedBigInteger('usuario_id')->nullable(false)->change();
            $table->dropColumn('correo_institucional');
        });
    }
};