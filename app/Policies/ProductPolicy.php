<?php

namespace App\Policies;

use App\Models\User;

class ProductPolicy
{
    public function manage(User $user): bool
    {
        return $user->isAdminOrManager();
    }

    public function admin(User $user): bool
    {
        return $user->isSuperAdmin();
    }
}
