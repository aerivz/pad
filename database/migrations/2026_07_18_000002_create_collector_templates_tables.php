<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plantillas_colector', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 80)->unique();
            $table->string('nombre', 120);
            $table->string('descripcion', 255)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('plantillas_colector_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plantilla_id')->constrained('plantillas_colector')->cascadeOnDelete();
            $table->string('nombre', 120);
            $table->decimal('porcentaje', 5, 2);
            $table->enum('tipo_calculo', ['normal', 'laboratorio']);
            $table->unsignedTinyInteger('cantidad_notas');
            $table->unsignedInteger('orden')->default(1);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        $templateId = DB::table('plantillas_colector')->insertGetId([
            'codigo' => 'base_general',
            'nombre' => 'Base general',
            'descripcion' => 'Plantilla academica general usada en la mayoria de materias.',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('plantillas_colector_detalle')->insert([
            ['plantilla_id' => $templateId, 'nombre' => 'Tareas', 'porcentaje' => 10, 'tipo_calculo' => 'normal', 'cantidad_notas' => 4, 'orden' => 1, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['plantilla_id' => $templateId, 'nombre' => 'Examenes', 'porcentaje' => 25, 'tipo_calculo' => 'normal', 'cantidad_notas' => 4, 'orden' => 2, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['plantilla_id' => $templateId, 'nombre' => 'Laboratorios', 'porcentaje' => 20, 'tipo_calculo' => 'laboratorio', 'cantidad_notas' => 2, 'orden' => 3, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['plantilla_id' => $templateId, 'nombre' => 'Actividades', 'porcentaje' => 15, 'tipo_calculo' => 'normal', 'cantidad_notas' => 4, 'orden' => 4, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['plantilla_id' => $templateId, 'nombre' => 'Expresion Oral y Escrita', 'porcentaje' => 10, 'tipo_calculo' => 'normal', 'cantidad_notas' => 4, 'orden' => 5, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['plantilla_id' => $templateId, 'nombre' => 'Participacion', 'porcentaje' => 10, 'tipo_calculo' => 'normal', 'cantidad_notas' => 4, 'orden' => 6, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['plantilla_id' => $templateId, 'nombre' => 'Dominio Conceptual y Semantica', 'porcentaje' => 10, 'tipo_calculo' => 'normal', 'cantidad_notas' => 4, 'orden' => 7, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        if (Schema::hasTable('menus') && Schema::hasTable('rol_menu') && Schema::hasTable('roles')) {
            $gradebookMenuId = DB::table('menus')->where('clave', 'gradebook')->value('id');

            $menuId = DB::table('menus')->insertGetId([
                'parent_id' => $gradebookMenuId,
                'clave' => 'collector_templates',
                'nombre' => 'Plantillas de notas',
                'descripcion' => 'Catalogo de plantillas para colector de notas.',
                'icono' => 'fas fa-layer-group',
                'url' => '/pad/plantillas-colector',
                'tablas_relacionadas' => 'plantillas_colector, plantillas_colector_detalle, materias, categorias_evaluacion',
                'orden' => 71,
                'activo' => true,
            ]);

            $roles = DB::table('roles')->pluck('id', 'nombre');
            $assignments = ['admin', 'secretaria', 'profesor'];

            $rows = collect($assignments)
                ->map(fn ($roleName) => $roles[$roleName] ?? null)
                ->filter()
                ->map(fn ($roleId) => ['rol_id' => $roleId, 'menu_id' => $menuId])
                ->values()
                ->all();

            if ($rows !== []) {
                DB::table('rol_menu')->insertOrIgnore($rows);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('rol_menu') && Schema::hasTable('menus')) {
            $menuId = DB::table('menus')->where('clave', 'collector_templates')->value('id');

            if ($menuId) {
                DB::table('rol_menu')->where('menu_id', $menuId)->delete();
                DB::table('menus')->where('id', $menuId)->delete();
            }
        }

        Schema::dropIfExists('plantillas_colector_detalle');
        Schema::dropIfExists('plantillas_colector');
    }
};
