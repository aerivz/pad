<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trimestres', function (Blueprint $table): void {
            $table->date('fecha_inicio')->nullable()->after('numero');
        });
    }

    public function down(): void
    {
        Schema::table('trimestres', function (Blueprint $table): void {
            $table->dropColumn('fecha_inicio');
        });
    }
};
