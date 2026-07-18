<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('secciones', function (Blueprint $table) {
            $table->foreignId('titular_profesor_id')
                ->nullable()
                ->after('anio_escolar')
                ->constrained('profesores')
                ->nullOnDelete();
        });

        $teacherRoleId = DB::table('roles')->where('nombre', 'profesor')->value('id');

        if (! $teacherRoleId) {
            return;
        }

        $teachers = DB::table('profesores')
            ->where('activo', true)
            ->orderBy('id')
            ->get();

        foreach ($teachers as $teacher) {
            $userId = $teacher->usuario_id;

            if (! $userId) {
                $existingUser = DB::table('usuarios')
                    ->where('email', $teacher->email)
                    ->first();

                if ($existingUser) {
                    $userId = $existingUser->id;

                    DB::table('usuarios')
                        ->where('id', $existingUser->id)
                        ->update([
                            'rol_id' => $teacherRoleId,
                            'nombres' => $teacher->nombres,
                            'apellidos' => $teacher->apellidos,
                            'activo' => true,
                        ]);
                } else {
                    $username = $this->uniqueUsername(
                        Str::slug(Str::before($teacher->email, '@'), '')
                            ?: Str::slug($teacher->nombres.$teacher->apellidos, '')
                            ?: 'profesor'.$teacher->id
                    );

                    $userId = DB::table('usuarios')->insertGetId([
                        'rol_id' => $teacherRoleId,
                        'nombre_usuario' => $username,
                        'email' => $teacher->email,
                        'password_hash' => Hash::make('123456'),
                        'nombres' => $teacher->nombres,
                        'apellidos' => $teacher->apellidos,
                        'activo' => true,
                        'created_at' => now(),
                    ]);
                }

                DB::table('profesores')
                    ->where('id', $teacher->id)
                    ->update(['usuario_id' => $userId]);
            } else {
                DB::table('usuarios')
                    ->where('id', $userId)
                    ->update([
                        'rol_id' => $teacherRoleId,
                        'email' => $teacher->email,
                        'nombres' => $teacher->nombres,
                        'apellidos' => $teacher->apellidos,
                        'activo' => true,
                    ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('secciones', function (Blueprint $table) {
            $table->dropConstrainedForeignId('titular_profesor_id');
        });
    }

    private function uniqueUsername(string $base): string
    {
        $base = Str::lower(preg_replace('/[^a-zA-Z0-9]+/', '', $base) ?: 'profesor');
        $candidate = Str::limit($base, 60, '');
        $suffix = 1;

        while (DB::table('usuarios')->where('nombre_usuario', $candidate)->exists()) {
            $tail = (string) $suffix;
            $candidate = Str::limit($base, 60 - strlen($tail), '').$tail;
            $suffix++;
        }

        return $candidate;
    }
};
