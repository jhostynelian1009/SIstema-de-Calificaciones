<?php

namespace App\Policies;

use App\Models\Subject;
use App\Models\User;

class SubjectPolicy
{
    /**
     * Determine whether the user can view any subjects.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the subject.
     */
    public function view(User $user, Subject $subject): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can create subjects.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the subject.
     */
    public function update(User $user, Subject $subject): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can toggle the active status of the subject.
     */
    public function toggleStatus(User $user, Subject $subject): bool
    {
        return $user->isAdmin();
    }
}
