<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isStaff()) {
            return $this->staffView($request);
        }

        $date = $request->date ? \Carbon\Carbon::parse($request->date) : today();

        $query = Attendance::with('user')
            ->whereDate('date', $date);

        if ($request->staff_id) {
            $query->where('user_id', $request->staff_id);
        }

        $attendances = $query->get();

        $allStaff = User::where('is_active', true)
            ->whereIn('role', ['staff', 'manager'])
            ->orderBy('name')
            ->get();

        // Find staff without attendance record for selected date
        $presentIds = $attendances->pluck('user_id')->toArray();
        $absentStaff = $allStaff->whereNotIn('id', $presentIds);

        $stats = [
            'present' => $attendances->where('status', 'present')->count(),
            'absent' => $absentStaff->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'half_day' => $attendances->where('status', 'half_day')->count(),
            'leave' => $attendances->where('status', 'leave')->count(),
        ];

        $staffList = User::whereIn('role', ['staff', 'manager'])->where('is_active', true)->get();

        return view('attendance.index', compact('attendances', 'allStaff', 'absentStaff', 'stats', 'date', 'staffList'));
    }

    private function staffView(Request $request)
    {
        $month = $request->month ? \Carbon\Carbon::parse($request->month . '-01') : now()->startOfMonth();

        $attendances = Attendance::where('user_id', Auth::id())
            ->whereYear('date', $month->year)
            ->whereMonth('date', $month->month)
            ->orderBy('date')
            ->get();

        $todayAttendance = Attendance::where('user_id', Auth::id())->today()->first();

        $stats = [
            'present' => $attendances->whereIn('status', ['present', 'late'])->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'half_day' => $attendances->where('status', 'half_day')->count(),
        ];

        return view('attendance.staff', compact('attendances', 'todayAttendance', 'stats', 'month'));
    }

    public function checkIn(Request $request)
    {
        $existing = Attendance::where('user_id', Auth::id())->today()->first();

        if ($existing) {
            return back()->with('error', 'You have already checked in today.');
        }

        Attendance::create([
            'user_id' => Auth::id(),
            'date' => today(),
            'time_in' => now()->format('H:i:s'),
            'status' => 'present',
            'ip_address' => $request->ip(),
            'marked_by' => Auth::id(),
        ]);

        AuditLog::record('check_in', 'attendance', 'Checked in at ' . now()->format('H:i'));

        return back()->with('success', 'Checked in successfully at ' . now()->format('h:i A'));
    }

    public function checkOut(Request $request)
    {
        $attendance = Attendance::where('user_id', Auth::id())->today()->first();

        if (!$attendance) {
            return back()->with('error', 'No check-in record found for today.');
        }

        if ($attendance->time_out) {
            return back()->with('error', 'You have already checked out today.');
        }

        $attendance->update(['time_out' => now()->format('H:i:s')]);
        AuditLog::record('check_out', 'attendance', 'Checked out at ' . now()->format('H:i'));

        return back()->with('success', 'Checked out successfully at ' . now()->format('h:i A'));
    }

    public function mark(Request $request)
    {
        $this->authorize('manage', Attendance::class);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,late,half_day,holiday,leave',
            'time_in' => 'nullable|date_format:H:i',
            'time_out' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string|max:500',
        ]);

        Attendance::updateOrCreate(
            ['user_id' => $validated['user_id'], 'date' => $validated['date']],
            array_merge($validated, ['marked_by' => Auth::id()])
        );

        $staffName = User::find($validated['user_id'])->name;
        AuditLog::record('marked', 'attendance', "Marked {$staffName} as {$validated['status']} on {$validated['date']}");

        return redirect()->back()->with('success', 'Attendance marked successfully.');
    }

    public function bulkMark(Request $request)
    {
        $this->authorize('manage', Attendance::class);

        $validated = $request->validate([
            'date' => 'required|date',
            'attendances' => 'required|array',
            'attendances.*.user_id' => 'required|exists:users,id',
            'attendances.*.status' => 'required|in:present,absent,late,half_day,holiday,leave',
            'attendances.*.time_in' => 'nullable|date_format:H:i',
            'attendances.*.time_out' => 'nullable|date_format:H:i',
        ]);

        foreach ($validated['attendances'] as $att) {
            Attendance::updateOrCreate(
                ['user_id' => $att['user_id'], 'date' => $validated['date']],
                array_merge($att, ['marked_by' => Auth::id()])
            );
        }

        AuditLog::record('bulk_marked', 'attendance', "Bulk marked attendance for {$validated['date']}");

        return redirect()->back()->with('success', 'Attendance saved for all staff.');
    }

    public function report(Request $request)
    {
        $this->authorize('view', Attendance::class);

        $from = $request->from ? \Carbon\Carbon::parse($request->from) : now()->startOfMonth();
        $to = $request->to ? \Carbon\Carbon::parse($request->to) : now()->endOfMonth();
        $staffId = $request->staff_id;

        $query = Attendance::with('user')
            ->whereBetween('date', [$from, $to]);

        if ($staffId) {
            $query->where('user_id', $staffId);
        }

        $records = $query->orderBy('date')->orderBy('user_id')->get();

        $staffList = User::whereIn('role', ['staff', 'manager'])->where('is_active', true)->get();

        // Summary per staff
        $summary = $records->groupBy('user_id')->map(function ($recs) {
            return [
                'user' => $recs->first()->user,
                'present' => $recs->whereIn('status', ['present', 'late'])->count(),
                'absent' => $recs->where('status', 'absent')->count(),
                'late' => $recs->where('status', 'late')->count(),
                'half_day' => $recs->where('status', 'half_day')->count(),
                'leave' => $recs->where('status', 'leave')->count(),
                'total_days' => $recs->count(),
            ];
        });

        return view('attendance.report', compact('records', 'summary', 'staffList', 'from', 'to', 'staffId'));
    }
}
