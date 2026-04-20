<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->insert([
            ['id' => 1, 'nombre' => 'admin', 'descripcion' => 'Acceso total al sistema', 'activo' => 1],
            ['id' => 2, 'nombre' => 'director', 'descripcion' => 'Visualización de reportes', 'activo' => 1],
            ['id' => 3, 'nombre' => 'secretaria', 'descripcion' => 'Gestión académica', 'activo' => 1],
            ['id' => 4, 'nombre' => 'profesor', 'descripcion' => 'Ingreso de notas', 'activo' => 1],
            ['id' => 5, 'nombre' => 'padre', 'descripcion' => 'Consulta de notas', 'activo' => 1],
        ]);

        DB::table('usuarios')->insert([
            [
                'id' => 1,
                'rol_id' => 1,
                'nombre_usuario' => 'admin',
                'email' => 'admin@colegio.sv',
                'password_hash' => Hash::make('123456'),
                'nombres' => 'Administrador',
                'apellidos' => 'General',
                'activo' => 1,
            ],
            [
                'id' => 2,
                'rol_id' => 4,
                'nombre_usuario' => 'gmorales',
                'email' => 'g.morales@edu.sv',
                'password_hash' => Hash::make('123456'),
                'nombres' => 'Gloria Elena',
                'apellidos' => 'Morales Rivas',
                'activo' => 1,
            ],
            [
                'id' => 3,
                'rol_id' => 4,
                'nombre_usuario' => 'hrivas',
                'email' => 'h.rivas@edu.sv',
                'password_hash' => Hash::make('123456'),
                'nombres' => 'Hernán Mauricio',
                'apellidos' => 'Rivas Domínguez',
                'activo' => 1,
            ],
        ]);

        DB::table('profesores')->insert([
            ['id' => 1, 'usuario_id' => 2, 'nombres' => 'Gloria Elena', 'apellidos' => 'Morales Rivas', 'email' => 'g.morales@edu.sv', 'especialidad' => 'Lenguaje', 'activo' => 1],
            ['id' => 2, 'usuario_id' => 3, 'nombres' => 'Hernán Mauricio', 'apellidos' => 'Rivas Domínguez', 'email' => 'h.rivas@edu.sv', 'especialidad' => 'Matemática', 'activo' => 1],
            ['id' => 3, 'usuario_id' => null, 'nombres' => 'Fernando Luis', 'apellidos' => 'Castillo Peñate', 'email' => 'f.castillo@edu.sv', 'especialidad' => 'Ciencias', 'activo' => 1],
            ['id' => 4, 'usuario_id' => null, 'nombres' => 'Patricia Noemí', 'apellidos' => 'Vásquez Muñoz', 'email' => 'p.vasquez@edu.sv', 'especialidad' => 'Sociales', 'activo' => 1],
            ['id' => 5, 'usuario_id' => null, 'nombres' => 'Miguel Ángel', 'apellidos' => 'López Hernández', 'email' => 'm.lopez@edu.sv', 'especialidad' => 'Inglés', 'activo' => 1],
        ]);

        DB::table('secciones')->insert([
            ['id' => 1, 'nombre' => 'A', 'grado' => 'Primer Grado', 'anio_escolar' => 2025],
            ['id' => 2, 'nombre' => 'B', 'grado' => 'Segundo Grado', 'anio_escolar' => 2025],
            ['id' => 3, 'nombre' => 'A', 'grado' => 'Tercer Grado', 'anio_escolar' => 2025],
        ]);

        DB::table('alumnos')->insert([
            ['id' => 1, 'seccion_id' => 1, 'nombres' => 'Carlos Andrés', 'apellidos' => 'Martínez López'],
            ['id' => 2, 'seccion_id' => 1, 'nombres' => 'Daniela Sofía', 'apellidos' => 'Ramírez Cruz'],
            ['id' => 3, 'seccion_id' => 2, 'nombres' => 'José Miguel', 'apellidos' => 'Hernández Flores'],
            ['id' => 4, 'seccion_id' => 2, 'nombres' => 'María Fernanda', 'apellidos' => 'Castro Díaz'],
            ['id' => 5, 'seccion_id' => 3, 'nombres' => 'Luis Alejandro', 'apellidos' => 'Morales García'],
        ]);

        DB::table('materias')->insert([
            ['id' => 1, 'nombre' => 'Lenguaje y Literatura'],
            ['id' => 2, 'nombre' => 'Matemática'],
            ['id' => 3, 'nombre' => 'Ciencias Naturales'],
            ['id' => 4, 'nombre' => 'Estudios Sociales'],
            ['id' => 5, 'nombre' => 'Inglés'],
        ]);

        DB::table('trimestres')->insert([
            ['id' => 1, 'nombre' => 'Primer Trimestre', 'numero' => 1],
            ['id' => 2, 'nombre' => 'Segundo Trimestre', 'numero' => 2],
            ['id' => 3, 'nombre' => 'Tercer Trimestre', 'numero' => 3],
        ]);

        DB::table('categorias_evaluacion')->insert([
            ['id' => 1, 'nombre' => 'Tareas', 'porcentaje' => 30.00],
            ['id' => 2, 'nombre' => 'Exámenes', 'porcentaje' => 40.00],
            ['id' => 3, 'nombre' => 'Participación', 'porcentaje' => 30.00],
        ]);

        DB::table('asignaciones')->insert([
            ['id' => 1, 'seccion_id' => 1, 'materia_id' => 1, 'profesor_id' => 1, 'anio_escolar' => 2025],
            ['id' => 2, 'seccion_id' => 1, 'materia_id' => 2, 'profesor_id' => 2, 'anio_escolar' => 2025],
            ['id' => 3, 'seccion_id' => 2, 'materia_id' => 1, 'profesor_id' => 1, 'anio_escolar' => 2025],
            ['id' => 4, 'seccion_id' => 3, 'materia_id' => 2, 'profesor_id' => 2, 'anio_escolar' => 2025],
            ['id' => 5, 'seccion_id' => 2, 'materia_id' => 3, 'profesor_id' => 3, 'anio_escolar' => 2025],
            ['id' => 6, 'seccion_id' => 3, 'materia_id' => 5, 'profesor_id' => 5, 'anio_escolar' => 2025],
        ]);

        DB::table('notas')->insert([
            ['id' => 1, 'alumno_id' => 1, 'asignacion_id' => 1, 'trimestre_id' => 1, 'categoria_id' => 1, 'valor' => 85.00],
            ['id' => 2, 'alumno_id' => 1, 'asignacion_id' => 1, 'trimestre_id' => 1, 'categoria_id' => 2, 'valor' => 90.00],
            ['id' => 3, 'alumno_id' => 1, 'asignacion_id' => 1, 'trimestre_id' => 1, 'categoria_id' => 3, 'valor' => 88.00],
            ['id' => 4, 'alumno_id' => 2, 'asignacion_id' => 1, 'trimestre_id' => 1, 'categoria_id' => 1, 'valor' => 92.00],
            ['id' => 5, 'alumno_id' => 2, 'asignacion_id' => 1, 'trimestre_id' => 1, 'categoria_id' => 2, 'valor' => 95.00],
            ['id' => 6, 'alumno_id' => 2, 'asignacion_id' => 1, 'trimestre_id' => 1, 'categoria_id' => 3, 'valor' => 91.00],
            ['id' => 7, 'alumno_id' => 1, 'asignacion_id' => 2, 'trimestre_id' => 1, 'categoria_id' => 1, 'valor' => 78.00],
            ['id' => 8, 'alumno_id' => 1, 'asignacion_id' => 2, 'trimestre_id' => 1, 'categoria_id' => 2, 'valor' => 84.00],
            ['id' => 9, 'alumno_id' => 1, 'asignacion_id' => 2, 'trimestre_id' => 1, 'categoria_id' => 3, 'valor' => 80.00],
            ['id' => 10, 'alumno_id' => 3, 'asignacion_id' => 3, 'trimestre_id' => 1, 'categoria_id' => 1, 'valor' => 75.00],
            ['id' => 11, 'alumno_id' => 3, 'asignacion_id' => 3, 'trimestre_id' => 1, 'categoria_id' => 2, 'valor' => 79.00],
            ['id' => 12, 'alumno_id' => 3, 'asignacion_id' => 3, 'trimestre_id' => 1, 'categoria_id' => 3, 'valor' => 81.00],
            ['id' => 13, 'alumno_id' => 4, 'asignacion_id' => 5, 'trimestre_id' => 1, 'categoria_id' => 1, 'valor' => 89.00],
            ['id' => 14, 'alumno_id' => 4, 'asignacion_id' => 5, 'trimestre_id' => 1, 'categoria_id' => 2, 'valor' => 86.00],
            ['id' => 15, 'alumno_id' => 4, 'asignacion_id' => 5, 'trimestre_id' => 1, 'categoria_id' => 3, 'valor' => 90.00],
            ['id' => 16, 'alumno_id' => 5, 'asignacion_id' => 4, 'trimestre_id' => 1, 'categoria_id' => 1, 'valor' => 83.00],
            ['id' => 17, 'alumno_id' => 5, 'asignacion_id' => 4, 'trimestre_id' => 1, 'categoria_id' => 2, 'valor' => 87.00],
            ['id' => 18, 'alumno_id' => 5, 'asignacion_id' => 4, 'trimestre_id' => 1, 'categoria_id' => 3, 'valor' => 84.00],
        ]);

        DB::table('padres')->insert([
            ['id' => 1, 'nombres' => 'Roberto', 'apellidos' => 'Martínez', 'email_principal' => 'roberto@email.com'],
            ['id' => 2, 'nombres' => 'Ana Lucía', 'apellidos' => 'Ramírez', 'email_principal' => 'ana@email.com'],
        ]);

        DB::table('padre_alumno')->insert([
            ['id' => 1, 'padre_id' => 1, 'alumno_id' => 1, 'parentesco' => 'Padre'],
            ['id' => 2, 'padre_id' => 2, 'alumno_id' => 2, 'parentesco' => 'Madre'],
        ]);

        DB::table('plantillas_correo')->insert([
            ['id' => 1, 'nombre' => 'reporte_trimestral', 'asunto' => 'Reporte de Notas', 'cuerpo_html' => 'Contenido HTML'],
            ['id' => 2, 'nombre' => 'nota_individual', 'asunto' => 'Nueva Nota', 'cuerpo_html' => 'Contenido HTML'],
        ]);

        DB::table('envios_correo')->insert([
            ['id' => 1, 'plantilla_id' => 1, 'padre_id' => 1, 'alumno_id' => 1, 'trimestre_id' => 1, 'estado' => 'enviado'],
            ['id' => 2, 'plantilla_id' => 1, 'padre_id' => 2, 'alumno_id' => 2, 'trimestre_id' => 1, 'estado' => 'pendiente'],
        ]);

        DB::table('auditoria_notas')->insert([
            ['id' => 1, 'nota_id' => 1, 'usuario_id' => 1, 'valor_anterior' => null, 'valor_nuevo' => 85.00, 'accion' => 'INSERT'],
        ]);

        if (DB::getSchemaBuilder()->hasTable('menus') && DB::getSchemaBuilder()->hasTable('rol_menu')) {
            $menus = DB::table('menus')->pluck('id', 'clave');
            $roles = DB::table('roles')->pluck('id', 'nombre');

            $assignments = [
                'admin' => ['dashboard', 'sections', 'students', 'teachers', 'subjects', 'guardians', 'gradebook', 'reportcard', 'emails', 'users', 'profiles', 'config'],
                'director' => ['dashboard', 'sections', 'students', 'teachers', 'subjects', 'guardians', 'reportcard', 'emails'],
                'secretaria' => ['dashboard', 'sections', 'students', 'teachers', 'subjects', 'guardians', 'gradebook', 'reportcard', 'emails'],
                'profesor' => ['dashboard', 'students', 'subjects', 'gradebook', 'reportcard'],
                'padre' => ['dashboard', 'reportcard', 'emails'],
            ];

            $rows = [];

            foreach ($assignments as $roleName => $menuKeys) {
                $roleId = $roles[$roleName] ?? null;

                if (! $roleId) {
                    continue;
                }

                foreach ($menuKeys as $menuKey) {
                    $menuId = $menus[$menuKey] ?? null;

                    if ($menuId) {
                        $rows[] = ['rol_id' => $roleId, 'menu_id' => $menuId];
                    }
                }
            }

            if ($rows !== []) {
                DB::table('rol_menu')->insertOrIgnore($rows);
            }
        }
    }
}
