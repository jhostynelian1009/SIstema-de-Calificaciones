<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database idempotently.
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->warn('Los seeders de datos demostrativos están estrictamente deshabilitados en producción.');

            return;
        }

        $shouldSeedDemo = config('demo.seed_demo', false);

        if (! $shouldSeedDemo && ! app()->environment('testing')) {
            $this->command?->info('Seeders demostrativos desactivados (SEED_DEMO_DATA=false). Base de datos lista para datos reales.');

            return;
        }

        if ($shouldSeedDemo || app()->environment('testing')) {
            $this->call(DemoSeeder::class);
        }
    }
}
