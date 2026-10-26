<?php

namespace Tests\Feature;

use App\Jobs\GenerateSystemBackupJob;
use App\Models\SystemBackup;
use App\Models\User;
use App\Services\SystemBackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class SystemBackupQueueTest extends TestCase
{
    use RefreshDatabase;

    public function test_backup_request_uses_the_dedicated_backup_connection(): void
    {
        Queue::fake();
        $user = $this->adminWithBackupAccess();

        $this->actingAs($user)
            ->post('/backups')
            ->assertSessionHasNoErrors();

        $backup = SystemBackup::query()->firstOrFail();
        $this->assertSame('pendiente', $backup->estado);
        Queue::assertPushed(GenerateSystemBackupJob::class, function (GenerateSystemBackupJob $job) use ($backup): bool {
            return $job->backupId === $backup->id
                && $job->connection === 'backups'
                && $job->queue === 'backups';
        });
    }

    public function test_duplicate_backup_jobs_only_generate_once(): void
    {
        $backup = SystemBackup::query()->create([
            'nombre' => 'Respaldo de prueba',
            'estado' => 'pendiente',
        ]);
        $service = Mockery::mock(SystemBackupService::class);
        $service->shouldReceive('generate')->once();
        $job = new GenerateSystemBackupJob($backup->id);

        $job->handle($service);
        $job->handle($service);

        $this->assertDatabaseHas('backups_sistema', [
            'id' => $backup->id,
            'estado' => 'procesando',
        ]);
    }

    private function adminWithBackupAccess(): User
    {
        $roleId = DB::table('roles')->insertGetId([
            'nombre' => 'admin',
            'descripcion' => 'Administrador de prueba',
            'activo' => true,
        ]);
        $menuId = DB::table('menus')->where('clave', 'backups')->value('id');
        DB::table('rol_menu')->insert(['rol_id' => $roleId, 'menu_id' => $menuId]);
        $userId = DB::table('usuarios')->insertGetId([
            'rol_id' => $roleId,
            'nombre_usuario' => 'admin.backups',
            'email' => 'admin-backups@example.test',
            'password_hash' => Hash::make('Clave-Segura9'),
            'must_change_password' => false,
            'nombres' => 'Admin',
            'apellidos' => 'Backups',
            'activo' => true,
            'created_at' => now(),
        ]);

        return User::query()->findOrFail($userId);
    }
}
