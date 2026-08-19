<?php

namespace App\Services\Grades;

use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Partial;
use App\Models\TeachingAssignment;

class GradeCompletionService
{
    /**
     * Calculate grading completion metrics for a teaching assignment and partial.
     */
    public function calculateForPartial(TeachingAssignment $assignment, Partial $partial): array
    {
        // Active enrolled students for the assignment's course and period
        $activeStudents = Enrollment::where('course_id', $assignment->course_id)
            ->where('academic_period_id', $assignment->academic_period_id)
            ->where('active', true)
            ->whereHas('student', fn ($q) => $q->where('active', true))
            ->pluck('student_id');

        $activeStudentsCount = $activeStudents->count();

        // Active activities for this assignment and partial
        $activeActivities = Activity::where('teaching_assignment_id', $assignment->id)
            ->where('partial_id', $partial->id)
            ->where('active', true)
            ->get();

        $activeActivitiesCount = $activeActivities->count();

        $expectedGrades = $activeStudentsCount * $activeActivitiesCount;

        if ($expectedGrades === 0) {
            return [
                'active_students_count' => $activeStudentsCount,
                'active_activities_count' => $activeActivitiesCount,
                'expected_grades' => 0,
                'completed_grades' => 0,
                'pending_grades' => 0,
                'completion_percentage' => 0.00,
                'status' => 'empty',
                'status_label' => 'Sin datos para calificar',
                'status_badge_class' => 'bg-secondary',
            ];
        }

        $activeActivityIds = $activeActivities->pluck('id');

        $completedGrades = Grade::whereIn('activity_id', $activeActivityIds)
            ->whereIn('student_id', $activeStudents)
            ->count();

        $pendingGrades = max(0, $expectedGrades - $completedGrades);
        $completionPercentage = round(($completedGrades / $expectedGrades) * 100, 2);

        $status = 'grading_incomplete';
        $statusLabel = 'Calificaciones incompletas';
        $statusBadgeClass = 'bg-warning text-dark';

        if ($completedGrades === $expectedGrades) {
            $status = 'grading_complete';
            $statusLabel = 'Calificaciones completas';
            $statusBadgeClass = 'bg-success';
        }

        return [
            'active_students_count' => $activeStudentsCount,
            'active_activities_count' => $activeActivitiesCount,
            'expected_grades' => $expectedGrades,
            'completed_grades' => $completedGrades,
            'pending_grades' => $pendingGrades,
            'completion_percentage' => $completionPercentage,
            'status' => $status,
            'status_label' => $statusLabel,
            'status_badge_class' => $statusBadgeClass,
        ];
    }

    /**
     * Calculate grading completion metrics for a single activity.
     */
    public function calculateForActivity(Activity $activity): array
    {
        $assignment = $activity->teachingAssignment;

        $activeStudents = Enrollment::where('course_id', $assignment->course_id)
            ->where('academic_period_id', $assignment->academic_period_id)
            ->where('active', true)
            ->whereHas('student', fn ($q) => $q->where('active', true))
            ->pluck('student_id');

        $activeStudentsCount = $activeStudents->count();

        if ($activeStudentsCount === 0 || ! $activity->active) {
            return [
                'active_students_count' => $activeStudentsCount,
                'completed_grades' => 0,
                'pending_grades' => 0,
                'completion_percentage' => 0.00,
                'status' => 'empty',
                'status_label' => 'Sin datos para calificar',
                'status_badge_class' => 'bg-secondary',
            ];
        }

        $completedGrades = Grade::where('activity_id', $activity->id)
            ->whereIn('student_id', $activeStudents)
            ->count();

        $pendingGrades = max(0, $activeStudentsCount - $completedGrades);
        $completionPercentage = round(($completedGrades / $activeStudentsCount) * 100, 2);

        $status = 'grading_incomplete';
        $statusLabel = 'Calificaciones incompletas';
        $statusBadgeClass = 'bg-warning text-dark';

        if ($completedGrades === $activeStudentsCount) {
            $status = 'grading_complete';
            $statusLabel = 'Calificaciones completas';
            $statusBadgeClass = 'bg-success';
        }

        return [
            'active_students_count' => $activeStudentsCount,
            'completed_grades' => $completedGrades,
            'pending_grades' => $pendingGrades,
            'completion_percentage' => $completionPercentage,
            'status' => $status,
            'status_label' => $statusLabel,
            'status_badge_class' => $statusBadgeClass,
        ];
    }
}
