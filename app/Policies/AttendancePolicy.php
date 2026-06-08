<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\SalaryRecord;
use App\Models\User;

class AttendancePolicy
{
    public function manage(User $user): bool
    {
        return $user->isAdminOrManager();
    }

    public function view(User $user): bool
    {
        return $user->isAdminOrManager();
    }
}
