<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\SalaryRecord;
use Illuminate\Http\Request;

class AttendanceApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::with('user');

        if ($request->user()->isStaff()) {
            $query->where('user_id', $request->user()->id);
        } else {
            if ($request->user_id) $query->where('user_id', $request->user_id);
        }

        if ($request->date) $query->whereDate('date', $request->date);
        if ($request->month) $query->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$request->month]);

        $records = $query->orderByDesc('date')->paginate($request->per_page ?? 30);
        return response()->json($records);
    }

    public function checkIn(Request $request)
    {
        $existing = Attendance::where('user_id', $request->user()->id)->today()->first();

        if ($existing) {
            return response()->json(['message' => 'Already checked in today.'], 422);
        }

        $attendance = Attendance::create([
            'user_id' => $request->user()->id,
            'date' => today(),
            'time_in' => now()->format('H:i:s'),
            'status' => 'present',
            'ip_address' => $request->ip(),
            'marked_by' => $request->user()->id,
        ]);

        return response()->json(['attendance' => $attendance, 'message' => 'Checked in successfully.'], 201);
    }

    public function checkOut(Request $request)
    {
        $attendance = Attendance::where('user_id', $request->user()->id)->today()->first();

        if (!$attendance) return response()->json(['message' => 'No check-in found.'], 422);
        if ($attendance->time_out) return response()->json(['message' => 'Already checked out.'], 422);

        $attendance->update(['time_out' => now()->format('H:i:s')]);
        return response()->json(['attendance' => $attendance, 'message' => 'Checked out successfully.']);
    }

    public function today(Request $request)
    {
        $attendance = Attendance::where('user_id', $request->user()->id)->today()->first();
        return response()->json(['attendance' => $attendance]);
    }
}
