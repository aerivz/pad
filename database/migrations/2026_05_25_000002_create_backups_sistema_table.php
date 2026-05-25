<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backups_sistema', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->string('nombre', 150);
            $table->string('archivo_zip', 255)->nullable();
            $table->enum('estado', ['pendiente', 'procesando', 'completado', 'fallido'])->default('pendiente');
            $table->unsignedBigInteger('tamano_bytes')->nullable();
            $table->unsignedInteger('total_archivos')->nullable();
            $table->json('metadatos')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backups_sistema');
    }
};
