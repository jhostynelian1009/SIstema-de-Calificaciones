<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the admin dashboard with core metric counts and active period status.
     */
    public function index(): View
    {
        $coursesCount = Course::count();
        $subjectsCount = Subject::count();
        $periodsCount = AcademicPeriod::count();
        $activePeriod = AcademicPeriod::where('active', true)->with('partials')->first();
        $usersCount = User::count();

        $activeEnrollmentsCount = 0;
        $activeAssignmentsCount = 0;

        if ($activePeriod) {
            $activeEnrollmentsCount = Enrollment::where('academic_period_id', $activePeriod->id)
                ->where('active', true)
                ->count();

            $activeAssignmentsCount = TeachingAssignment::where('academic_period_id', $activePeriod->id)
                ->where('active', true)
                ->count();
        }

        return view('admin.dashboard', compact(
            'coursesCount',
            'subjectsCount',
            'periodsCount',
            'activePeriod',
            'usersCount',
            'activeEnrollmentsCount',
            'activeAssignmentsCount'
        ));
    }
}
