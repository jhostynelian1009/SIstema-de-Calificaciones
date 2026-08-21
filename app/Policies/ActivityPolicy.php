<?php

namespace App\Policies;

use App\Models\Activity;
use App\Models\TeachingAssignment;
use App\Models\User;

class ActivityPolicy
{
    /**
     * Determine whether the user can view any activities.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isTeacher();
    }

    /**
     * Determine whether the user can view a specific activity.
     */
    public function view(User $user, Activity $activity): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isTeacher()) {
            return (int) $activity->teachingAssignment->teacher_id === (int) $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create an activity for a teaching assignment.
     */
    public function create(User $user, TeachingAssignment $assignment): bool
    {
        if (! $user->isTeacher()) {
            return false;
        }

        if (! $assignment->active) {
            return false;
        }

        return (int) $assignment->teacher_id === (int) $user->id;
    }

    /**
     * Determine whether the user can update an activity.
     */
    public function update(User $user, Activity $activity): bool
    {
        if (! $user->isTeacher()) {
            return false;
        }

        $assignment = $activity->teachingAssignment;
        if (! $assignment || ! $assignment->active) {
            return false;
        }

        return (int) $assignment->teacher_id === (int) $user->id;
    }

    /**
     * Determine whether the user can toggle status (active/inactive) of an activity.
     */
    public function toggleStatus(User $user, Activity $activity): bool
    {
        if (! $user->isTeacher()) {
            return false;
        }

        $assignment = $activity->teachingAssignment;
        if (! $assignment || ! $assignment->active) {
            return false;
        }

        return (int) $assignment->teacher_id === (int) $user->id;
    }
}
