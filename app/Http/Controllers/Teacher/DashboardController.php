<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Services\Grades\PartialPublicationStateService;
use App\Services\Teacher\TeacherDashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected TeacherDashboardService $dashboardService;

    public function __construct(TeacherDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Display the Teacher Dashboard with active assignments, publication state counts, and priority actions.
     */
    public function index(): View
    {
        $user = Auth::user();

        // Ensure publication states are initialized for all teacher active assignments
        $assignments = $user->teachingAssignments()->active()->get();
        $publicationService = app(PartialPublicationStateService::class);
        foreach ($assignments as $assignment) {
            $publicationService->ensureForAssignment($assignment);
        }

        $metrics = $this->dashboardService->getMetrics($user);

        return view('teacher.dashboard', array_merge(['user' => $user], $metrics));
    }
}
