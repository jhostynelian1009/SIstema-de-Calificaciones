<?php

namespace App\Services\Student;

use App\Models\AcademicPeriod;
use App\Models\Enrollment;
use App\Models\User;

class StudentDashboardService
{
    protected StudentResultsService $resultsService;

    public function __construct(StudentResultsService $resultsService)
    {
        $this->resultsService = $resultsService;
    }

    /**
     * Get comprehensive dashboard metrics for the student.
     */
    public function getMetrics(User $student): array
    {
        $availablePeriods = $this->resultsService->getAvailablePeriods($student);

        if ($availablePeriods->isEmpty()) {
            return [
                'has_enrollment' => false,
                'active_period' => null,
                'enrollment' => null,
                'available_periods' => collect(),
                'subjects_count' => 0,
                'available_results_count' => 0,
                'pending_results_count' => 0,
                'official_final_count' => 0,
                'general_result' => null,
                'is_general_official' => false,
                'subjects_summary' => [],
            ];
        }

        // Active academic period or latest enrolled period
        $activePeriod = AcademicPeriod::where('active', true)->first();
        $targetPeriod = $activePeriod && $availablePeriods->contains('id', $activePeriod->id)
            ? $activePeriod
            : $availablePeriods->first();

        $summary = $this->resultsService->getSubjectsSummaryForPeriod($student, $targetPeriod);

        if (! $summary) {
            return [
                'has_enrollment' => false,
                'active_period' => $targetPeriod,
                'enrollment' => null,
                'available_periods' => $availablePeriods,
                'subjects_count' => 0,
                'available_results_count' => 0,
                'pending_results_count' => 0,
                'official_final_count' => 0,
                'general_result' => null,
                'is_general_official' => false,
                'subjects_summary' => [],
            ];
        }

        $subjects = $summary['subjects'];
        $subjectsCount = count($subjects);

        $availableResultsCount = 0;
        $pendingResultsCount = 0;
        $officialFinalCount = 0;

        foreach ($subjects as $s) {
            if ($s['p1_published']) {
                $availableResultsCount++;
            } else {
                $pendingResultsCount++;
            }

            if ($s['p2_published']) {
                $availableResultsCount++;
            } else {
                $pendingResultsCount++;
            }

            if ($s['final_result'] !== null) {
                $officialFinalCount++;
            }
        }

        return [
            'has_enrollment' => true,
            'active_period' => $targetPeriod,
            'enrollment' => $summary['enrollment'],
            'course' => $summary['course'],
            'available_periods' => $availablePeriods,
            'subjects_count' => $subjectsCount,
            'available_results_count' => $availableResultsCount,
            'pending_results_count' => $pendingResultsCount,
            'official_final_count' => $officialFinalCount,
            'general_result' => $summary['general_result'],
            'is_general_official' => $summary['is_general_official'],
            'subjects_summary' => $subjects,
        ];
    }
}
