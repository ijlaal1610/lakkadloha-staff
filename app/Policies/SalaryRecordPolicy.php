<?php

namespace App\Policies;

use App\Models\SalaryRecord;
use App\Models\User;

class SalaryRecordPolicy
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
