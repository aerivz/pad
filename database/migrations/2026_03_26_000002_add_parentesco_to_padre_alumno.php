<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('padre_alumno', function (Blueprint $table) {
            $table->string('parentesco', 30)->default('Padre')->after('alumno_id');
        });

        DB::table('padre_alumno')
            ->whereNull('parentesco')
            ->update(['parentesco' => 'Padre']);
    }

    public function down(): void
    {
        Schema::table('padre_alumno', function (Blueprint $table) {
            $table->dropColumn('parentesco');
        });
    }
};
