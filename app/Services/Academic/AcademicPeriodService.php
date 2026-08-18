<?php

namespace App\Services\Academic;

use App\Models\AcademicPeriod;
use App\Models\Partial;
use Illuminate\Support\Facades\DB;

class AcademicPeriodService
{
    /**
     * Create an AcademicPeriod and automatically create P1 and P2 inside a transaction.
     */
    public function createPeriod(array $data): AcademicPeriod
    {
        return DB::transaction(function () use ($data) {
            $active = !empty($data['active']);

            if ($active) {
                AcademicPeriod::where('active', true)->update(['active' => false]);
            }

            $period = AcademicPeriod::create([
                'name' => trim($data['name']),
                'starts_at' => $data['starts_at'],
                'ends_at' => $data['ends_at'],
                'active' => $active,
            ]);

            // Create Partial 1
            $p1 = Partial::create([
                'academic_period_id' => $period->id,
                'number' => 1,
                'name' => 'Primer parcial',
                'weight' => 50.00,
            ]);

            // Create Partial 2
            $p2 = Partial::create([
                'academic_period_id' => $period->id,
                'number' => 2,
                'name' => 'Segundo parcial',
                'weight' => 50.00,
            ]);

            if (!$p1 || !$p2) {
                throw new \RuntimeException('No se pudieron generar los parciales P1 y P2.');
            }

            return $period->fresh(['partials']);
        });
    }

    /**
     * Activate an AcademicPeriod, deactivating any currently active period inside a transaction.
     */
    public function activatePeriod(AcademicPeriod $period): void
    {
        DB::transaction(function () use ($period) {
            AcademicPeriod::where('active', true)
                ->where('id', '!=', $period->id)
                ->update(['active' => false]);

            $period->update(['active' => true]);
        });
    }

    /**
     * Update an AcademicPeriod name, dates, and active status inside a transaction without altering partials.
     */
    public function updatePeriod(AcademicPeriod $period, array $data): AcademicPeriod
    {
        return DB::transaction(function () use ($period, $data) {
            $active = !empty($data['active']);

            if ($active && !$period->active) {
                AcademicPeriod::where('active', true)
                    ->where('id', '!=', $period->id)
                    ->update(['active' => false]);
            }

            $period->update([
                'name' => trim($data['name']),
                'starts_at' => $data['starts_at'],
                'ends_at' => $data['ends_at'],
                'active' => $active,
            ]);

            return $period->fresh(['partials']);
        });
    }
}
