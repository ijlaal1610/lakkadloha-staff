<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Attendance;

class UserPolicy
{
    public function admin(User $user): bool
    {
        return $user->isAdminOrManager();
    }

    public function superAdmin(User $user): bool
    {
        return $user->isSuperAdmin();
    }
}
