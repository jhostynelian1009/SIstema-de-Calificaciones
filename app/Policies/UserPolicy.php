<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any users.
     */
    public function viewAny(User $actor): bool
    {
        return $actor->isAdmin();
    }

    /**
     * Determine whether the user can view the user model.
     */
    public function view(User $actor, User $user): bool
    {
        return $actor->isAdmin();
    }

    /**
     * Determine whether the user can create users.
     */
    public function create(User $actor): bool
    {
        return $actor->isAdmin();
    }

    /**
     * Determine whether the user can update the user model.
     */
    public function update(User $actor, User $user): bool
    {
        return $actor->isAdmin();
    }

    /**
     * Determine whether the user can toggle status of the user model.
     */
    public function toggleStatus(User $actor, User $user): bool
    {
        return $actor->isAdmin();
    }

    /**
     * Determine whether the user can change role of the user model.
     */
    public function changeRole(User $actor, User $user): bool
    {
        return $actor->isAdmin();
    }

    /**
     * Determine whether the user can reset password for the user model.
     */
    public function resetPassword(User $actor, User $user): bool
    {
        return $actor->isAdmin();
    }
}
