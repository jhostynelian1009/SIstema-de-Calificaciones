<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the Teacher Dashboard.
     */
    public function index(): View
    {
        return view('teacher.dashboard', [
            'user' => Auth::user(),
        ]);
    }
}
