<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Redirect to central dashboard route.
     */
    public function index(): RedirectResponse
    {
        return redirect()->route('dashboard');
    }
}
