<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    /**
     * Determine whether the user can view any courses.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the course.
     */
    public function view(User $user, Course $course): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can create courses.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the course.
     */
    public function update(User $user, Course $course): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can toggle the active status of the course.
     */
    public function toggleStatus(User $user, Course $course): bool
    {
        return $user->isAdmin();
    }
}
