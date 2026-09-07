<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->string('avatar', 255)->nullable()->after('apellidos');
            $table->string('telefono', 30)->nullable()->after('avatar');
            $table->string('ubicacion', 150)->nullable()->after('telefono');
            $table->text('biografia')->nullable()->after('ubicacion');
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn(['avatar', 'telefono', 'ubicacion', 'biografia']);
        });
    }
};
