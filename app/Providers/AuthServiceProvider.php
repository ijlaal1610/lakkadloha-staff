<?php

namespace App\Providers;

use App\Models\Attendance;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SalaryRecord;
use App\Models\User;
use App\Policies\AttendancePolicy;
use App\Policies\ProductPolicy;
use App\Policies\SalePolicy;
use App\Policies\SalaryRecordPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Product::class => ProductPolicy::class,
        Sale::class => SalePolicy::class,
        User::class => UserPolicy::class,
        Attendance::class => AttendancePolicy::class,
        SalaryRecord::class => SalaryRecordPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
