<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\Enrollment;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the Student Dashboard with active period and enrollment info.
     */
    public function index(): View
    {
        $user = Auth::user();

        $activePeriod = AcademicPeriod::where('active', true)->first();

        $enrollment = null;
        if ($activePeriod) {
            $enrollment = Enrollment::with('course')
                ->where('student_id', $user->id)
                ->where('academic_period_id', $activePeriod->id)
                ->first();
        }

        return view('student.dashboard', [
            'user' => $user,
            'activePeriod' => $activePeriod,
            'enrollment' => $enrollment,
        ]);
    }
}
