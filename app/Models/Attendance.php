<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'date', 'time_in', 'time_out',
        'status', 'notes', 'ip_address', 'marked_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function marker() { return $this->belongsTo(User::class, 'marked_by'); }

    public function getWorkingHoursAttribute(): ?string
    {
        if (!$this->time_in || !$this->time_out) return null;
        $in = \Carbon\Carbon::parse($this->time_in);
        $out = \Carbon\Carbon::parse($this->time_out);
        $diff = $in->diff($out);
        return $diff->format('%H:%I');
    }

    public function getWorkingMinutesAttribute(): int
    {
        if (!$this->time_in || !$this->time_out) return 0;
        $in = \Carbon\Carbon::parse($this->date->format('Y-m-d') . ' ' . $this->time_in);
        $out = \Carbon\Carbon::parse($this->date->format('Y-m-d') . ' ' . $this->time_out);
        return $in->diffInMinutes($out);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'present' => 'bg-success',
            'absent' => 'bg-danger',
            'late' => 'bg-warning',
            'half_day' => 'bg-info',
            'holiday' => 'bg-secondary',
            'leave' => 'bg-primary',
            default => 'bg-secondary',
        };
    }

    public function scopeToday($query) { return $query->whereDate('date', today()); }
    public function scopeForUser($query, $userId) { return $query->where('user_id', $userId); }
}
