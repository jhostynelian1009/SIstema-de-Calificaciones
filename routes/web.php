<?php

use App\Http\Controllers\Admin\AcademicPeriodController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EnrollmentController as AdminEnrollmentController;
use App\Http\Controllers\Admin\PartialPublicationController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TeachingAssignmentController as AdminTeachingAssignmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\TeachingAssignmentController as TeacherTeachingAssignmentController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
});

// Authentication routes (Public registration disabled)
Auth::routes(['register' => false]);

// Protected Routes (Authentication & Active User required)
Route::middleware(['auth', 'active'])->group(function () {
    // Central Dashboard Redirector
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admin Area
    Route::middleware(['role:admin'])->prefix('admin')->as('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Courses Management
        Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
        Route::get('/courses/create', [CourseController::class, 'create'])->name('courses.create');
        Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
        Route::get('/courses/{course}/edit', [CourseController::class, 'edit'])->name('courses.edit');
        Route::put('/courses/{course}', [CourseController::class, 'update'])->name('courses.update');
        Route::patch('/courses/{course}/toggle-status', [CourseController::class, 'toggleStatus'])->name('courses.toggle-status');

        // Subjects Management
        Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
        Route::get('/subjects/create', [SubjectController::class, 'create'])->name('subjects.create');
        Route::post('/subjects', [SubjectController::class, 'store'])->name('subjects.store');
        Route::get('/subjects/{subject}/edit', [SubjectController::class, 'edit'])->name('subjects.edit');
        Route::put('/subjects/{subject}', [SubjectController::class, 'update'])->name('subjects.update');
        Route::patch('/subjects/{subject}/toggle-status', [SubjectController::class, 'toggleStatus'])->name('subjects.toggle-status');

        // Academic Periods Management
        Route::get('/academic-periods', [AcademicPeriodController::class, 'index'])->name('academic-periods.index');
        Route::get('/academic-periods/create', [AcademicPeriodController::class, 'create'])->name('academic-periods.create');
        Route::post('/academic-periods', [AcademicPeriodController::class, 'store'])->name('academic-periods.store');
        Route::get('/academic-periods/{academic_period}/edit', [AcademicPeriodController::class, 'edit'])->name('academic-periods.edit');
        Route::put('/academic-periods/{academic_period}', [AcademicPeriodController::class, 'update'])->name('academic-periods.update');
        Route::patch('/academic-periods/{academic_period}/activate', [AcademicPeriodController::class, 'activate'])->name('academic-periods.activate');
        Route::patch('/academic-periods/{academic_period}/toggle-status', [AcademicPeriodController::class, 'toggleStatus'])->name('academic-periods.toggle-status');

        // Enrollments Management
        Route::get('/enrollments', [AdminEnrollmentController::class, 'index'])->name('enrollments.index');
        Route::get('/enrollments/create', [AdminEnrollmentController::class, 'create'])->name('enrollments.create');
        Route::post('/enrollments', [AdminEnrollmentController::class, 'store'])->name('enrollments.store');
        Route::get('/enrollments/{enrollment}/edit', [AdminEnrollmentController::class, 'edit'])->name('enrollments.edit');
        Route::put('/enrollments/{enrollment}', [AdminEnrollmentController::class, 'update'])->name('enrollments.update');
        Route::patch('/enrollments/{enrollment}/toggle-status', [AdminEnrollmentController::class, 'toggleStatus'])->name('enrollments.toggle-status');

        // Teaching Assignments Management
        Route::get('/teaching-assignments', [AdminTeachingAssignmentController::class, 'index'])->name('teaching-assignments.index');
        Route::get('/teaching-assignments/create', [AdminTeachingAssignmentController::class, 'create'])->name('teaching-assignments.create');
        Route::post('/teaching-assignments', [AdminTeachingAssignmentController::class, 'store'])->name('teaching-assignments.store');
        Route::get('/teaching-assignments/{teaching_assignment}/edit', [AdminTeachingAssignmentController::class, 'edit'])->name('teaching-assignments.edit');
        Route::put('/teaching-assignments/{teaching_assignment}', [AdminTeachingAssignmentController::class, 'update'])->name('teaching-assignments.update');
        Route::patch('/teaching-assignments/{teaching_assignment}/toggle-status', [AdminTeachingAssignmentController::class, 'toggleStatus'])->name('teaching-assignments.toggle-status');

        // Partial Publications View (Read-Only)
        Route::get('/partial-publications', [PartialPublicationController::class, 'index'])->name('partial-publications.index');
    });

    // Teacher Area
    Route::middleware(['role:teacher'])->prefix('teacher')->as('teacher.')->group(function () {
        Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');
        Route::get('/assignments', [TeacherTeachingAssignmentController::class, 'index'])->name('assignments.index');
        Route::get('/assignments/{assignment}', [TeacherTeachingAssignmentController::class, 'show'])->name('assignments.show');
    });

    // Student Area
    Route::middleware(['role:student'])->prefix('student')->as('student.')->group(function () {
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
    });

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
});
