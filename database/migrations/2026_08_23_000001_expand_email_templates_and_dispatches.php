<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plantillas_correo', function (Blueprint $table) {
            $table->string('descripcion', 255)->nullable()->after('nombre');
            $table->json('documentos_generados')->nullable()->after('cuerpo_html');
        });

        Schema::create('plantilla_correo_rol', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plantilla_id')->constrained('plantillas_correo');
            $table->foreignId('rol_id')->constrained('roles');
            $table->unique(['plantilla_id', 'rol_id'], 'uq_plantilla_correo_rol');
        });

        Schema::table('envios_correo', function (Blueprint $table) {
            $table->foreignId('usuario_id')->nullable()->after('id')->constrained('usuarios');
            $table->string('destinatario_email', 150)->nullable()->after('estado');
            $table->json('adjuntos_generados')->nullable()->after('destinatario_email');
            $table->text('error_mensaje')->nullable()->after('adjuntos_generados');
            $table->timestamp('enviado_en')->nullable()->after('error_mensaje');
        });
    }

    public function down(): void
    {
        Schema::table('envios_correo', function (Blueprint $table) {
            $table->dropConstrainedForeignId('usuario_id');
            $table->dropColumn(['destinatario_email', 'adjuntos_generados', 'error_mensaje', 'enviado_en']);
        });

        Schema::dropIfExists('plantilla_correo_rol');

        Schema::table('plantillas_correo', function (Blueprint $table) {
            $table->dropColumn(['descripcion', 'documentos_generados']);
        });
    }
};
