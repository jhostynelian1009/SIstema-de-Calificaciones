<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminResetUserPasswordRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\Users\UserService;
use Exception;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of users with filters and pagination.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::query();

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        if ($request->has('active') && $request->input('active') !== null && $request->input('active') !== '') {
            $query->where('active', (bool) $request->input('active'));
        }

        $users = $query->orderBy('name')->paginate(25)->withQueryString();
        $roles = UserRole::cases();

        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $this->authorize('create', User::class);
        $roles = UserRole::cases();

        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(StoreUserRequest $request)
    {
        try {
            $user = $this->userService->createUser($request->validated(), $request->user());

            return redirect()->route('admin.users.index')
                ->with('success', "El usuario \"{$user->name}\" ha sido registrado exitosamente.");
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Display details of a specific user.
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);

        $user->load([
            'studentEnrollments.course',
            'studentEnrollments.academicPeriod',
            'teachingAssignments.course',
            'teachingAssignments.subject',
            'teachingAssignments.academicPeriod',
        ]);

        $hasStudentHistory = $user->hasStudentHistory();
        $hasTeacherHistory = $user->hasTeacherHistory();

        return view('admin.users.show', compact('user', 'hasStudentHistory', 'hasTeacherHistory'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);
        $roles = UserRole::cases();

        $hasStudentHistory = $user->hasStudentHistory();
        $hasTeacherHistory = $user->hasTeacherHistory();

        return view('admin.users.edit', compact('user', 'roles', 'hasStudentHistory', 'hasTeacherHistory'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        try {
            $updatedUser = $this->userService->updateUser($user, $request->validated(), $request->user());

            return redirect()->route('admin.users.index')
                ->with('success', "El usuario \"{$updatedUser->name}\" ha sido actualizado exitosamente.");
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Toggle status (active/inactive) of a user.
     */
    public function toggleStatus(Request $request, User $user)
    {
        $this->authorize('toggleStatus', $user);

        try {
            $updatedUser = $this->userService->toggleStatus($user, $request->user());
            $statusLabel = $updatedUser->active ? 'activado' : 'desactivado';

            return redirect()->back()
                ->with('success', "El usuario \"{$updatedUser->name}\" ha sido {$statusLabel} exitosamente.");
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reset password of a user by admin.
     */
    public function resetPassword(AdminResetUserPasswordRequest $request, User $user)
    {
        try {
            $this->userService->resetPassword($user, $request->password, $request->user());

            return redirect()->back()
                ->with('success', "La contraseña del usuario \"{$user->name}\" ha sido restablecida exitosamente.");
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
}
