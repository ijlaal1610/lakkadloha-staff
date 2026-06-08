<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'phone', 'address', 'avatar', 'password',
        'role', 'is_active', 'employee_id', 'joining_date', 'designation',
        'failed_login_attempts', 'locked_until',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'joining_date' => 'date',
        'locked_until' => 'datetime',
    ];

    // Role helpers
    public function isSuperAdmin(): bool { return $this->role === 'super_admin'; }
    public function isManager(): bool { return $this->role === 'manager'; }
    public function isStaff(): bool { return $this->role === 'staff'; }
    public function isAdminOrManager(): bool { return in_array($this->role, ['super_admin', 'manager']); }

    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    public function getRoleLabel(): string
    {
        return match($this->role) {
            'super_admin' => 'Super Admin',
            'manager' => 'Manager',
            'staff' => 'Staff',
            default => ucfirst($this->role),
        };
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('public/storage/' . $this->avatar);
        }
        $name = urlencode($this->name);
        return "https://ui-avatars.com/api/?name={$name}&background=4f46e5&color=fff&size=128";
    }

    // Relationships
    public function loginHistories() { return $this->hasMany(LoginHistory::class); }
    public function sales() { return $this->hasMany(Sale::class, 'staff_id'); }
    public function attendances() { return $this->hasMany(Attendance::class); }
    public function salaryRecords() { return $this->hasMany(SalaryRecord::class, 'employee_id'); }
    public function products() { return $this->hasMany(Product::class, 'created_by'); }
    public function auditLogs() { return $this->hasMany(AuditLog::class); }

    // Salary helpers
    public function totalSalaryPaid(): float
    {
        return $this->salaryRecords()->where('type', 'salary')->sum('amount');
    }

    public function totalAdvancesTaken(): float
    {
        return $this->salaryRecords()->where('type', 'advance')->sum('amount');
    }
}
