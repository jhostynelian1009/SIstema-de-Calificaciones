<?php

namespace App\Policies;

use App\Enums\PublicationStatus;
use App\Models\PartialPublication;
use App\Models\User;

class PartialPublicationPolicy
{
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
     * Determine whether the user can preview the publication readiness and provisional grades.
     */
    public function preview(User $user, PartialPublication $publication): bool
    {
        return $this->view($user, $publication);
    }

    /**
     * Determine whether the user can publish or republish a partial.
     * Only the assigned active teacher can publish (Admin cannot publish in ordinary flow).
     */
    public function publish(User $user, PartialPublication $publication): bool
    {
        if (! $user->isTeacher()) {
            return false;
        }

        $assignment = $publication->teachingAssignment;
        if (! $assignment || ! $assignment->active) {
            return false;
        }

        if ((int) $assignment->teacher_id !== (int) $user->id) {
            return false;
        }

        return in_array($publication->status, [PublicationStatus::Draft, PublicationStatus::Reopened], true);
    }

    /**
     * Determine whether the user can reopen a published partial.
     * Only Admin can reopen, and only if status is Currently Published.
     */
    public function reopen(User $user, PartialPublication $publication): bool
    {
        if (! $user->isAdmin()) {
            return false;
        }

        return $publication->status === PublicationStatus::Published;
    }
}
