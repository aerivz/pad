<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void { Schema::create('configuraciones_sistema', function (Blueprint $table) { $table->id(); $table->string('clave', 100)->unique(); $table->text('valor')->nullable(); $table->boolean('cifrado')->default(false); $table->timestamps(); }); }
    public function down(): void { Schema::dropIfExists('configuraciones_sistema'); }
};
