<?php

namespace App\Services\Admin;

use App\Enums\PublicationStatus;
use App\Enums\UserRole;
use App\Models\AcademicPeriod;
use App\Models\Activity;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\PartialPublication;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;
use App\Services\Grades\PartialReadinessService;

class AdminDashboardService
{
    protected PartialReadinessService $readinessService;

    public function __construct(PartialReadinessService $readinessService)
    {
        $this->readinessService = $readinessService;
    }

    /**
     * Compile comprehensive institutional metrics and alerts for admin dashboard.
     */
    public function getMetrics(): array
    {
        $activeAdmins = User::where('role', UserRole::Admin)->where('active', true)->count();
        $activeTeachers = User::where('role', UserRole::Teacher)->where('active', true)->count();
        $activeStudents = User::where('role', UserRole::Student)->where('active', true)->count();
        $inactiveUsers = User::where('active', false)->count();
        $totalUsers = User::count();

        $activeCourses = Course::where('active', true)->count();
        $activeSubjects = Subject::where('active', true)->count();
        $activePeriod = AcademicPeriod::where('active', true)->with('partials')->first();

        $activeEnrollmentsCount = 0;
        $activeAssignmentsCount = 0;
        $activeActivitiesCount = 0;
        $recordedGradesCount = 0;

        $draftPartialsCount = 0;
        $publishedPartialsCount = 0;
        $reopenedPartialsCount = 0;
        $readyPartialsCount = 0;
        $incompletePartialsCount = 0;

        $reopenedPartialsList = collect();
        $readyUnpublishedList = collect();
        $coursesWithoutStudents = collect();
        $incompleteWeightingsList = collect();

        if ($activePeriod) {
            $activeEnrollmentsCount = Enrollment::where('academic_period_id', $activePeriod->id)
                ->where('active', true)
                ->count();

            $activeAssignmentsCount = TeachingAssignment::where('academic_period_id', $activePeriod->id)
                ->where('active', true)
                ->count();

            $assignments = TeachingAssignment::where('academic_period_id', $activePeriod->id)
                ->where('active', true)
                ->with(['course', 'subject', 'teacher', 'partialPublications.partial'])
                ->get();

            $assignmentIds = $assignments->pluck('id');

            $activeActivitiesCount = Activity::whereIn('teaching_assignment_id', $assignmentIds)
                ->where('active', true)
                ->count();

            $recordedGradesCount = Grade::whereHas('activity', function ($q) use ($assignmentIds) {
                $q->whereIn('teaching_assignment_id', $assignmentIds)->where('active', true);
            })->count();

            $publications = PartialPublication::whereIn('teaching_assignment_id', $assignmentIds)
                ->with(['teachingAssignment.course', 'teachingAssignment.subject', 'teachingAssignment.teacher', 'partial'])
                ->get();

            $draftPartialsCount = $publications->where('status', PublicationStatus::Draft)->count();
            $publishedPartialsCount = $publications->where('status', PublicationStatus::Published)->count();
            $reopenedPartialsCount = $publications->where('status', PublicationStatus::Reopened)->count();
            $reopenedPartialsList = $publications->where('status', PublicationStatus::Reopened);

            // Calculate readiness per publication
            foreach ($assignments as $assignment) {
                foreach ($activePeriod->partials as $partial) {
                    $readiness = $this->readinessService->checkReadiness($assignment, $partial);
                    $pub = $publications->first(fn ($p) => $p->teaching_assignment_id === $assignment->id && $p->partial_id === $partial->id);

                    if ($readiness['is_ready']) {
                        if (! $pub || $pub->status !== PublicationStatus::Published) {
                            $readyPartialsCount++;
                            $readyUnpublishedList->push([
                                'assignment' => $assignment,
                                'partial' => $partial,
                                'publication' => $pub,
                            ]);
                        }
                    } else {
                        if (! $pub || $pub->status !== PublicationStatus::Published) {
                            $incompletePartialsCount++;
                        }
                        if (in_array('Las actividades registradas no suman el 100% (suma actual: 100.00%).', $readiness['pending_issues']) || str_contains(implode(' ', $readiness['pending_issues']), 'suman el 100%')) {
                            $incompleteWeightingsList->push([
                                'assignment' => $assignment,
                                'partial' => $partial,
                                'issues' => $readiness['pending_issues'],
                            ]);
                        }
                    }
                }
            }

            // Courses without students alert
            $coursesInPeriod = Course::where('active', true)->get();
            foreach ($coursesInPeriod as $c) {
                $studentCount = Enrollment::where('course_id', $c->id)
                    ->where('academic_period_id', $activePeriod->id)
                    ->where('active', true)
                    ->count();
                if ($studentCount === 0) {
                    $coursesWithoutStudents->push($c);
                }
            }
        }

        return [
            'users' => [
                'active_admins' => $activeAdmins,
                'active_teachers' => $activeTeachers,
                'active_students' => $activeStudents,
                'inactive_users' => $inactiveUsers,
                'total_users' => $totalUsers,
            ],
            'structure' => [
                'active_courses' => $activeCourses,
                'active_subjects' => $activeSubjects,
                'active_period' => $activePeriod,
                'active_enrollments' => $activeEnrollmentsCount,
                'active_assignments' => $activeAssignmentsCount,
            ],
            'evaluation' => [
                'active_activities' => $activeActivitiesCount,
                'recorded_grades' => $recordedGradesCount,
                'draft_partials' => $draftPartialsCount,
                'published_partials' => $publishedPartialsCount,
                'reopened_partials' => $reopenedPartialsCount,
                'ready_partials_count' => $readyPartialsCount,
                'incomplete_partials_count' => $incompletePartialsCount,
            ],
            'alerts' => [
                'no_active_period' => ($activePeriod === null),
                'courses_without_students' => $coursesWithoutStudents,
                'reopened_partials_list' => $reopenedPartialsList,
                'ready_unpublished_list' => $readyUnpublishedList,
                'incomplete_weightings' => $incompleteWeightingsList,
            ],
        ];
    }
}
