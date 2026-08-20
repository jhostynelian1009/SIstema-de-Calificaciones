<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\Student\StudentDashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected StudentDashboardService $dashboardService;

    public function __construct(StudentDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Display the Student Dashboard with scoped metrics and quick actions.
     */
    public function index(Request $request)
    {
        $student = $request->user();
        $metrics = $this->dashboardService->getMetrics($student);

        return response()
            ->view('student.dashboard', compact('metrics'))
            ->header('Cache-Control', 'private, no-store')
            ->header('Pragma', 'no-cache')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
