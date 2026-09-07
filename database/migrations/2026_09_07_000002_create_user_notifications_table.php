<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones_usuarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->string('tipo', 30)->default('info');
            $table->string('titulo', 150);
            $table->text('mensaje');
            $table->string('url', 255)->nullable();
            $table->timestamp('leida_en')->nullable();
            $table->timestamps();
            $table->index(['usuario_id', 'leida_en'], 'idx_notificaciones_usuario_lectura');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones_usuarios');
    }
};
