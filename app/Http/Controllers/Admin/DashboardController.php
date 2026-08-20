<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminDashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    protected AdminDashboardService $dashboardService;

    public function __construct(AdminDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Show the admin dashboard with core metric counts and active period status.
     */
    public function index(): View
    {
        $metrics = $this->dashboardService->getMetrics();

        // Extract variables for view compatibility
        $coursesCount = $metrics['structure']['active_courses'];
        $subjectsCount = $metrics['structure']['active_subjects'];
        $activePeriod = $metrics['structure']['active_period'];
        $periodsCount = \App\Models\AcademicPeriod::count();
        $usersCount = $metrics['users']['total_users'];
        $activeEnrollmentsCount = $metrics['structure']['active_enrollments'];
        $activeAssignmentsCount = $metrics['structure']['active_assignments'];
        $draftCount = $metrics['evaluation']['draft_partials'];
        $publishedCount = $metrics['evaluation']['published_partials'];
        $reopenedCount = $metrics['evaluation']['reopened_partials'];
        $readyCount = $metrics['evaluation']['ready_partials_count'];
        $incompleteCount = $metrics['evaluation']['incomplete_partials_count'];
        $alerts = $metrics['alerts'];
        $userMetrics = $metrics['users'];

        return view('admin.dashboard', compact(
            'coursesCount',
            'subjectsCount',
            'periodsCount',
            'activePeriod',
            'usersCount',
            'activeEnrollmentsCount',
            'activeAssignmentsCount',
            'draftCount',
            'publishedCount',
            'reopenedCount',
            'readyCount',
            'incompleteCount',
            'alerts',
            'userMetrics'
        ));
    }
}
