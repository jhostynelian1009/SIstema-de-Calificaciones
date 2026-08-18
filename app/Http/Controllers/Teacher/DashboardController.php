<?php

namespace App\Http\Controllers\Teacher;

use App\Enums\PublicationStatus;
use App\Http\Controllers\Controller;
use App\Models\PartialPublication;
use App\Models\TeachingAssignment;
use App\Services\Grades\PartialPublicationStateService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the Teacher Dashboard with active assignments and publication state counts.
     */
    public function index(): View
    {
        $user = Auth::user();

        $assignments = TeachingAssignment::with(['course', 'subject', 'academicPeriod', 'partialPublications.partial'])
            ->assignedTo($user)
            ->active()
            ->get();

        // Ensure publication states are initialized for all teacher active assignments
        $publicationService = app(PartialPublicationStateService::class);
        foreach ($assignments as $assignment) {
            if ($assignment->partialPublications->isEmpty()) {
                $publicationService->ensureForAssignment($assignment);
            }
        }

        $assignmentIds = $assignments->pluck('id');

        $draftCount = PartialPublication::whereIn('teaching_assignment_id', $assignmentIds)
            ->where('status', PublicationStatus::Draft)
            ->count();

        $publishedCount = PartialPublication::whereIn('teaching_assignment_id', $assignmentIds)
            ->where('status', PublicationStatus::Published)
            ->count();

        $reopenedCount = PartialPublication::whereIn('teaching_assignment_id', $assignmentIds)
            ->where('status', PublicationStatus::Reopened)
            ->count();

        return view('teacher.dashboard', [
            'user' => $user,
            'assignments' => $assignments,
            'draftCount' => $draftCount,
            'publishedCount' => $publishedCount,
            'reopenedCount' => $reopenedCount,
        ]);
    }
}
