<?php

namespace App\Policies;

use App\Models\AcademicPeriod;
use App\Models\User;

class AcademicPeriodPolicy
{
    /**
     * Determine whether the user can view any academic periods.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the academic period.
     */
    public function view(User $user, AcademicPeriod $academicPeriod): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can create academic periods.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the academic period.
     */
    public function update(User $user, AcademicPeriod $academicPeriod): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can activate or toggle status of the academic period.
     */
    public function toggleStatus(User $user, AcademicPeriod $academicPeriod): bool
    {
        return $user->isAdmin();
    }
}
