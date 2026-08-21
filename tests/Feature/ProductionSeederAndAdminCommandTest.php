<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductionSeederAndAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function create_admin_command_creates_active_admin_safely()
    {
        $this->artisan('app:create-admin', [
            '--name' => 'Admin Producción',
            '--email' => 'admin.prod@calificaciones.com',
            '--password' => 'SecurePass123!',
        ])
            ->expectsOutputToContain('Usuario administrador creado exitosamente')
            ->assertExitCode(0);

        $this->assertDatabaseHas('users', [
            'name' => 'Admin Producción',
            'email' => 'admin.prod@calificaciones.com',
            'role' => UserRole::Admin->value,
            'active' => true,
        ]);

        $admin = User::where('email', 'admin.prod@calificaciones.com')->firstOrFail();
        $this->assertTrue(Hash::check('SecurePass123!', $admin->password));
    }

    #[Test]
    public function create_admin_command_rejects_duplicate_email()
    {
        User::factory()->create(['email' => 'existente@calificaciones.com']);

        $this->artisan('app:create-admin', [
            '--name' => 'Otro Admin',
            '--email' => 'existente@calificaciones.com',
            '--password' => 'Short',
        ])
            ->assertExitCode(1);
    }

    #[Test]
    public function database_seeder_warns_and_aborts_in_production_environment()
    {
        // Mock app environment as production
        $this->app['env'] = 'production';

        $initialUserCount = User::count();

        $seeder = new DatabaseSeeder;
        $seeder->setContainer($this->app);
        $seeder->run();

        // No new demo users should be created
        $this->assertEquals($initialUserCount, User::count());
    }
}
