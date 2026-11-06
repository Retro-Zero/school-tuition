<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

/**
 * One shared admin today, but authorisation has a home now: a viewer can read
 * every screen and change nothing.
 */
class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Student $student): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->canManage();
    }

    public function update(User $user, Student $student): bool
    {
        return $user->canManage();
    }

    public function delete(User $user, Student $student): bool
    {
        return $user->canManage();
    }
}
