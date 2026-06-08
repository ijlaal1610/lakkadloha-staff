<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginHistory extends Model
{
    protected $fillable = [
        'user_id', 'ip_address', 'user_agent', 'device_type',
        'browser', 'platform', 'status', 'logged_in_at', 'logged_out_at',
    ];

    protected $casts = [
        'logged_in_at' => 'datetime',
        'logged_out_at' => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class); }

    public static function parseUserAgent(string $ua): array
    {
        $browser = 'Unknown';
        $platform = 'Unknown';
        $device = 'Desktop';

        if (str_contains($ua, 'Mobile') || str_contains($ua, 'Android')) $device = 'Mobile';
        elseif (str_contains($ua, 'Tablet') || str_contains($ua, 'iPad')) $device = 'Tablet';

        if (str_contains($ua, 'Chrome')) $browser = 'Chrome';
        elseif (str_contains($ua, 'Firefox')) $browser = 'Firefox';
        elseif (str_contains($ua, 'Safari')) $browser = 'Safari';
        elseif (str_contains($ua, 'Edge')) $browser = 'Edge';

        if (str_contains($ua, 'Windows')) $platform = 'Windows';
        elseif (str_contains($ua, 'Mac')) $platform = 'macOS';
        elseif (str_contains($ua, 'Linux')) $platform = 'Linux';
        elseif (str_contains($ua, 'Android')) $platform = 'Android';
        elseif (str_contains($ua, 'iOS') || str_contains($ua, 'iPhone')) $platform = 'iOS';

        return compact('browser', 'platform', 'device');
    }
}
