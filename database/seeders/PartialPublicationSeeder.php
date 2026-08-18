<?php

namespace Database\Seeders;

use App\Services\Grades\PartialPublicationStateService;
use Illuminate\Database\Seeder;

class PartialPublicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $service = app(PartialPublicationStateService::class);
        $service->ensureForAllAssignments();
    }
}
