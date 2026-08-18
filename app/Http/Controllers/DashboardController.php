<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Redirect user to their role-specific dashboard.
     */
    public function index(): RedirectResponse
    {
        $user = Auth::user();

        return match ($user->role) {
            UserRole::Admin => redirect()->route('admin.dashboard'),
            UserRole::Teacher => redirect()->route('teacher.dashboard'),
            UserRole::Student => redirect()->route('student.dashboard'),
        };
    }
}
