<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\SalaryRecord;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalaryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isStaff()) {
            return $this->staffView($request);
        }

        $query = SalaryRecord::with(['employee', 'processor']);

        if ($request->employee_id) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->type) {
            $query->where('type', $request->type);
        }
        if ($request->date_from) {
            $query->whereDate('record_date', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('record_date', '<=', $request->date_to);
        }

        $records = $query->latest('record_date')->paginate(20)->withQueryString();

        $employees = User::whereIn('role', ['staff', 'manager'])->where('is_active', true)->orderBy('name')->get();

        $stats = [
            'total_salary_this_month' => SalaryRecord::where('type', 'salary')
                ->whereMonth('record_date', now()->month)
                ->whereYear('record_date', now()->year)
                ->sum('amount'),
            'total_advances_this_month' => SalaryRecord::where('type', 'advance')
                ->whereMonth('record_date', now()->month)
                ->whereYear('record_date', now()->year)
                ->sum('amount'),
            'total_bonuses_this_month' => SalaryRecord::where('type', 'bonus')
                ->whereMonth('record_date', now()->month)
                ->whereYear('record_date', now()->year)
                ->sum('amount'),
        ];

        return view('salary.index', compact('records', 'employees', 'stats'));
    }

    private function staffView(Request $request)
    {
        $records = SalaryRecord::where('employee_id', Auth::id())
            ->latest('record_date')
            ->paginate(15);

        $stats = [
            'total_salary' => SalaryRecord::where('employee_id', Auth::id())->where('type', 'salary')->sum('amount'),
            'total_advances' => SalaryRecord::where('employee_id', Auth::id())->where('type', 'advance')->sum('amount'),
            'total_bonuses' => SalaryRecord::where('employee_id', Auth::id())->where('type', 'bonus')->sum('amount'),
            'total_deductions' => SalaryRecord::where('employee_id', Auth::id())->where('type', 'deduction')->sum('amount'),
        ];

        return view('salary.staff', compact('records', 'stats'));
    }

    public function create()
    {
        $this->authorize('manage', SalaryRecord::class);
        $employees = User::whereIn('role', ['staff', 'manager'])->where('is_active', true)->orderBy('name')->get();
        return view('salary.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $this->authorize('manage', SalaryRecord::class);

        $validated = $request->validate([
            'employee_id' => 'required|exists:users,id',
            'type' => 'required|in:salary,advance,bonus,deduction',
            'amount' => 'required|numeric|min:1',
            'record_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,upi,cheque',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        $record = SalaryRecord::create(array_merge($validated, [
            'processed_by' => Auth::id(),
        ]));

        $employee = User::find($validated['employee_id']);
        AuditLog::record('created', 'salary',
            "Created {$validated['type']} of ₹{$validated['amount']} for {$employee->name}",
            $record);

        return redirect()->route('salary.index')
            ->with('success', "Salary record created successfully for {$employee->name}.");
    }

    public function show(SalaryRecord $salary)
    {
        if (Auth::user()->isStaff() && $salary->employee_id !== Auth::id()) {
            abort(403);
        }
        $salary->load(['employee', 'processor']);
        return view('salary.show', compact('salary'));
    }

    public function destroy(SalaryRecord $salary)
    {
        $this->authorize('admin', SalaryRecord::class);
        AuditLog::record('deleted', 'salary', "Deleted salary record #{$salary->id}");
        $salary->delete();
        return redirect()->route('salary.index')->with('success', 'Record deleted.');
    }

    public function employeeSummary(User $employee)
    {
        $this->authorize('manage', SalaryRecord::class);

        $records = SalaryRecord::where('employee_id', $employee->id)
            ->with('processor')
            ->latest('record_date')
            ->paginate(20);

        $stats = [
            'total_salary' => SalaryRecord::where('employee_id', $employee->id)->where('type', 'salary')->sum('amount'),
            'total_advances' => SalaryRecord::where('employee_id', $employee->id)->where('type', 'advance')->sum('amount'),
            'total_bonuses' => SalaryRecord::where('employee_id', $employee->id)->where('type', 'bonus')->sum('amount'),
            'total_deductions' => SalaryRecord::where('employee_id', $employee->id)->where('type', 'deduction')->sum('amount'),
        ];

        // Monthly breakdown
        $monthlyBreakdown = SalaryRecord::where('employee_id', $employee->id)
            ->selectRaw("DATE_FORMAT(record_date, '%Y-%m') as month, type, SUM(amount) as total")
            ->groupBy('month', 'type')
            ->orderByDesc('month')
            ->take(24)
            ->get();

        return view('salary.employee-summary', compact('employee', 'records', 'stats', 'monthlyBreakdown'));
    }
}
