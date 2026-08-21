<?php

namespace App\Policies;

use App\Enums\PublicationStatus;
use App\Models\Activity;
use App\Models\Grade;
use App\Models\PartialPublication;
use App\Models\TeachingAssignment;
use App\Models\User;

class GradePolicy
{
    /**
     * Determine whether the user can view any grades.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isTeacher();
    }

    /**
     * Determine whether the user can view the grade.
     */
    public function view(User $user, Grade $grade): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isTeacher()) {
            $assignment = $grade->activity->teachingAssignment;

            return (int) $assignment->teacher_id === (int) $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create grades for an activity or assignment.
     */
    public function create(User $user, Activity|TeachingAssignment|null $target = null): bool
    {
        if (! $user->isTeacher()) {
            return false;
        }

        if (! $target) {
            return true;
        }

        if ($target instanceof Activity) {
            $assignment = $target->teachingAssignment;
            $activity = $target;
        } else {
            $assignment = $target;
            $activity = null;
        }

        if (! $assignment || ! $assignment->active) {
            return false;
        }

        if ($activity && ! $activity->active) {
            return false;
        }

        if ((int) $assignment->teacher_id !== (int) $user->id) {
            return false;
        }

        if ($activity) {
            $publication = PartialPublication::where('teaching_assignment_id', $assignment->id)
                ->where('partial_id', $activity->partial_id)
                ->first();

            if ($publication && $publication->status === PublicationStatus::Published) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine whether the user can update the grade.
     */
    public function update(User $user, Grade $grade): bool
    {
        if (! $user->isTeacher()) {
            return false;
        }

        $activity = $grade->activity;
        $assignment = $activity->teachingAssignment;

        if (! $assignment->active || ! $activity->active) {
            return false;
        }

        if ((int) $assignment->teacher_id !== (int) $user->id) {
            return false;
        }

        $publication = PartialPublication::where('teaching_assignment_id', $assignment->id)
            ->where('partial_id', $activity->partial_id)
            ->first();

        if ($publication && $publication->status === PublicationStatus::Published) {
            return false;
        }

        return true;
    }
}
