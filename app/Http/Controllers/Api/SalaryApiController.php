<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SalaryRecord;
use Illuminate\Http\Request;

class SalaryApiController extends Controller
{
    public function index(Request $request)
    {
        $query = SalaryRecord::with(['employee', 'processor']);

        if ($request->user()->isStaff()) {
            $query->where('employee_id', $request->user()->id);
        } else {
            if ($request->employee_id) $query->where('employee_id', $request->employee_id);
        }

        if ($request->type) $query->where('type', $request->type);
        if ($request->month) $query->whereRaw("DATE_FORMAT(record_date, '%Y-%m') = ?", [$request->month]);

        $records = $query->orderByDesc('record_date')->paginate($request->per_page ?? 20);
        return response()->json($records);
    }

    public function store(Request $request)
    {
        if (!$request->user()->isAdminOrManager()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'employee_id' => 'required|exists:users,id',
            'type' => 'required|in:salary,advance,bonus,deduction',
            'amount' => 'required|numeric|min:1',
            'record_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,upi,cheque',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $record = SalaryRecord::create(array_merge($validated, ['processed_by' => $request->user()->id]));
        AuditLog::record('created', 'salary', "API: Salary record created", $record);

        return response()->json(['record' => $record->load(['employee', 'processor'])], 201);
    }

    public function summary(Request $request)
    {
        $user = $request->user();
        $employeeId = $user->isStaff() ? $user->id : ($request->employee_id ?? $user->id);

        return response()->json([
            'total_salary' => SalaryRecord::where('employee_id', $employeeId)->where('type', 'salary')->sum('amount'),
            'total_advances' => SalaryRecord::where('employee_id', $employeeId)->where('type', 'advance')->sum('amount'),
            'total_bonuses' => SalaryRecord::where('employee_id', $employeeId)->where('type', 'bonus')->sum('amount'),
            'total_deductions' => SalaryRecord::where('employee_id', $employeeId)->where('type', 'deduction')->sum('amount'),
        ]);
    }
}
