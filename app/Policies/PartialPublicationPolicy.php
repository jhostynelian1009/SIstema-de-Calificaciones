<?php

namespace App\Policies;

use App\Models\PartialPublication;
use App\Models\User;

class PartialPublicationPolicy
{
    /**
     * Perform pre-authorization checks.
     * Admin can view any partial publication state.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any partial publication states list.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isTeacher();
    }

    /**
     * Determine whether the user can view the specific partial publication state.
     */
    public function view(User $user, PartialPublication $publication): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isTeacher()) {
            return (int) $publication->teachingAssignment?->teacher_id === (int) $user->id
                && (bool) $publication->teachingAssignment?->active;
        }

        return false;
    }

    /**
     * Disallow update in K-005.
     */
    public function update(User $user, PartialPublication $publication): bool
    {
        return false;
    }
}
