<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class RealDataAndDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_does_not_run_when_seed_demo_data_is_false_in_local_env(): void
    {
        $this->app['env'] = 'local';
        Config::set('demo.seed_demo', false);

        Artisan::call('db:seed');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_demo_seeder_runs_when_seed_demo_data_is_true(): void
    {
        $this->app['env'] = 'testing';
        Config::set('demo.seed_demo', true);

        Artisan::call('db:seed');

        $this->assertDatabaseHas('users', [
            'email' => 'admin@calificaciones.local',
        ]);
    }

    public function test_demo_seeder_is_blocked_in_production_even_if_flag_is_true(): void
    {
        $this->app['env'] = 'production';
        Config::set('demo.seed_demo', true);

        Artisan::call('db:seed');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_prepare_real_data_command_blocks_production(): void
    {
        $this->app['env'] = 'production';

        $code = Artisan::call('app:prepare-real-data');

        $this->assertEquals(1, $code);
    }

    public function test_prepare_real_data_command_blocks_test_database(): void
    {
        $this->app['env'] = 'local';
        Config::set('database.connections.mysql.database', 'sistema_calificaciones_test');

        $code = Artisan::call('app:prepare-real-data');

        $this->assertEquals(1, $code);
    }

    public function test_prepare_real_data_command_fails_without_backup_file(): void
    {
        $this->app['env'] = 'local';
        Config::set('database.connections.mysql.database', 'sistema_calificaciones');

        // Clear backup directory for this test
        $backupDir = storage_path('app/private/backups');
        if (File::exists($backupDir)) {
            File::cleanDirectory($backupDir);
        }

        $code = Artisan::call('app:prepare-real-data');

        $this->assertEquals(1, $code);
    }
}
