<?php

namespace Database\Seeders;

use App\Models\AcademicPeriod;
use App\Models\Partial;
use App\Services\Academic\AcademicPeriodService;
use Illuminate\Database\Seeder;

class AcademicPeriodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $periodName = 'Período académico 2026–2027';

        $period = AcademicPeriod::where('name', $periodName)->first();

        if (! $period) {
            $service = new AcademicPeriodService;
            $service->createPeriod([
                'name' => $periodName,
                'starts_at' => '2026-09-01',
                'ends_at' => '2027-07-31',
                'active' => true,
            ]);
        } else {
            $period->update([
                'starts_at' => '2026-09-01',
                'ends_at' => '2027-07-31',
                'active' => true,
            ]);

            Partial::updateOrCreate(
                ['academic_period_id' => $period->id, 'number' => 1],
                ['name' => 'Primer parcial', 'weight' => 50.00]
            );

            Partial::updateOrCreate(
                ['academic_period_id' => $period->id, 'number' => 2],
                ['name' => 'Segundo parcial', 'weight' => 50.00]
            );
        }
    }
}
