<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'processed_by', 'type', 'amount',
        'notes', 'record_date', 'payment_method', 'reference_number',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'record_date' => 'date',
    ];

    public function employee() { return $this->belongsTo(User::class, 'employee_id'); }
    public function processor() { return $this->belongsTo(User::class, 'processed_by'); }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'salary' => 'Salary',
            'advance' => 'Advance',
            'bonus' => 'Bonus',
            'deduction' => 'Deduction',
            default => ucfirst($this->type),
        };
    }

    public function getTypeBadgeClassAttribute(): string
    {
        return match($this->type) {
            'salary' => 'bg-success',
            'advance' => 'bg-warning',
            'bonus' => 'bg-info',
            'deduction' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    public function isCredit(): bool
    {
        return in_array($this->type, ['salary', 'bonus']);
    }
}
