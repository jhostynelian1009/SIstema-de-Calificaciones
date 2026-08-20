<?php

namespace App\Policies;

use App\Models\TeachingAssignment;
use App\Models\User;

class TeachingAssignmentPolicy
{
    /**
     * Determine whether the user can view any teaching assignments.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isTeacher();
    }

    /**
     * Determine whether the user can view the specific teaching assignment (including historical inactive ones).
     */
    public function view(User $user, TeachingAssignment $assignment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isTeacher()) {
            return (int) $user->id === (int) $assignment->teacher_id;
        }

        return false;
    }

    /**
     * Determine whether the user can create teaching assignments.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the teaching assignment.
     */
    public function update(User $user, TeachingAssignment $assignment): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can toggle status of the teaching assignment.
     */
    public function toggleStatus(User $user, TeachingAssignment $assignment): bool
    {
        return $user->isAdmin();
    }
}
