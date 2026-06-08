<?php

namespace App\Policies;

use App\Models\Sale;
use App\Models\User;

class SalePolicy
{
    public function update(User $user, Sale $sale): bool
    {
        return $user->isAdminOrManager() || $sale->staff_id === $user->id;
    }

    public function manage(User $user): bool
    {
        return $user->isAdminOrManager();
    }

    public function viewReports(User $user): bool
    {
        return $user->isAdminOrManager();
    }
}
