<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lotes_envio_correo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->foreignId('plantilla_id')->constrained('plantillas_correo');
            $table->foreignId('seccion_id')->constrained('secciones');
            $table->foreignId('trimestre_id')->constrained('trimestres');
            $table->string('nombre', 150);
            $table->enum('estado', ['procesando', 'completado', 'completado_con_errores'])->default('procesando');
            $table->unsignedInteger('total')->default(0);
            $table->unsignedInteger('procesados')->default(0);
            $table->unsignedInteger('enviados')->default(0);
            $table->unsignedInteger('fallidos')->default(0);
            $table->unsignedInteger('omitidos')->default(0);
            $table->timestamp('iniciado_en')->nullable();
            $table->timestamp('finalizado_en')->nullable();
            $table->timestamp('notificado_en')->nullable();
            $table->timestamps();
        });

        Schema::table('envios_correo', function (Blueprint $table) {
            $table->foreignId('lote_id')->nullable()->after('id')->constrained('lotes_envio_correo')->nullOnDelete();
            $table->boolean('en_cola')->default(false)->after('estado');
            $table->index(['lote_id', 'estado'], 'idx_envios_lote_estado');
        });
    }

    public function down(): void
    {
        Schema::table('envios_correo', function (Blueprint $table) {
            $table->dropIndex('idx_envios_lote_estado');
            $table->dropConstrainedForeignId('lote_id');
            $table->dropColumn('en_cola');
        });

        Schema::dropIfExists('lotes_envio_correo');
    }
};
