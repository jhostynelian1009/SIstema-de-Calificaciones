<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the Student Dashboard.
     */
    public function index(): View
    {
        return view('student.dashboard', [
            'user' => Auth::user(),
        ]);
    }
}
