<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SalaryRecord;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesExport;
use App\Exports\AttendanceExport;

class ReportsController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::user()->isAdminOrManager()) abort(403);
            return $next($request);
        });
    }

    public function index()
    {
        return view('reports.index');
    }

    public function sales(Request $request)
    {
        $from = $request->from ? \Carbon\Carbon::parse($request->from) : now()->startOfMonth();
        $to = $request->to ? \Carbon\Carbon::parse($request->to) : now()->endOfMonth();
        $staffId = $request->staff_id;

        $query = Sale::with(['product', 'staff'])
            ->whereBetween('sold_at', [$from->startOfDay(), $to->endOfDay()]);

        if ($staffId) $query->where('staff_id', $staffId);

        $sales = $query->latest('sold_at')->get();

        $summary = [
            'total_revenue' => $sales->where('status', 'completed')->sum('total_amount'),
            'total_orders' => $sales->where('status', 'completed')->count(),
            'cancelled' => $sales->where('status', 'cancelled')->count(),
            'refunded' => $sales->where('status', 'refunded')->count(),
            'avg_order_value' => $sales->where('status', 'completed')->count() > 0
                ? $sales->where('status', 'completed')->sum('total_amount') / $sales->where('status', 'completed')->count()
                : 0,
        ];

        // Daily breakdown
        $daily = $sales->where('status', 'completed')
            ->groupBy(fn($s) => $s->sold_at->format('Y-m-d'))
            ->map(fn($group) => ['revenue' => $group->sum('total_amount'), 'count' => $group->count()])
            ->sortKeys();

        // Staff performance
        $staffPerformance = $sales->where('status', 'completed')
            ->groupBy('staff_id')
            ->map(fn($group) => [
                'staff' => $group->first()->staff,
                'revenue' => $group->sum('total_amount'),
                'count' => $group->count(),
            ])
            ->sortByDesc('revenue');

        // Top products
        $topProducts = $sales->where('status', 'completed')
            ->groupBy('product_id')
            ->map(fn($group) => [
                'product' => $group->first()->product,
                'revenue' => $group->sum('total_amount'),
                'quantity' => $group->sum('quantity'),
            ])
            ->sortByDesc('revenue')
            ->take(10);

        $staffList = User::whereIn('role', ['staff', 'manager'])->where('is_active', true)->get();

        return view('reports.sales', compact(
            'sales', 'summary', 'daily', 'staffPerformance', 'topProducts',
            'staffList', 'from', 'to', 'staffId'
        ));
    }

    public function attendance(Request $request)
    {
        $from = $request->from ? \Carbon\Carbon::parse($request->from) : now()->startOfMonth();
        $to = $request->to ? \Carbon\Carbon::parse($request->to) : now()->endOfMonth();

        $records = Attendance::with('user')
            ->whereBetween('date', [$from, $to])
            ->orderBy('date')
            ->get();

        $allStaff = User::whereIn('role', ['staff', 'manager'])->where('is_active', true)->get();

        $summary = $allStaff->map(function ($staff) use ($records) {
            $staffRecords = $records->where('user_id', $staff->id);
            return [
                'staff' => $staff,
                'present' => $staffRecords->whereIn('status', ['present', 'late'])->count(),
                'absent' => $staffRecords->where('status', 'absent')->count(),
                'late' => $staffRecords->where('status', 'late')->count(),
                'half_day' => $staffRecords->where('status', 'half_day')->count(),
                'leave' => $staffRecords->where('status', 'leave')->count(),
            ];
        });

        return view('reports.attendance', compact('records', 'summary', 'allStaff', 'from', 'to'));
    }

    public function salary(Request $request)
    {
        $from = $request->from ? \Carbon\Carbon::parse($request->from) : now()->startOfMonth();
        $to = $request->to ? \Carbon\Carbon::parse($request->to) : now()->endOfMonth();

        $records = SalaryRecord::with(['employee', 'processor'])
            ->whereBetween('record_date', [$from, $to])
            ->latest('record_date')
            ->get();

        $summary = [
            'total_salary' => $records->where('type', 'salary')->sum('amount'),
            'total_advances' => $records->where('type', 'advance')->sum('amount'),
            'total_bonuses' => $records->where('type', 'bonus')->sum('amount'),
            'total_deductions' => $records->where('type', 'deduction')->sum('amount'),
            'net_expense' => $records->whereIn('type', ['salary', 'bonus'])->sum('amount')
                - $records->where('type', 'deduction')->sum('amount'),
        ];

        $byEmployee = $records->groupBy('employee_id')->map(function ($recs) {
            return [
                'employee' => $recs->first()->employee,
                'salary' => $recs->where('type', 'salary')->sum('amount'),
                'advance' => $recs->where('type', 'advance')->sum('amount'),
                'bonus' => $recs->where('type', 'bonus')->sum('amount'),
                'deduction' => $recs->where('type', 'deduction')->sum('amount'),
            ];
        });

        return view('reports.salary', compact('records', 'summary', 'byEmployee', 'from', 'to'));
    }

    public function inventory(Request $request)
    {
        $products = Product::with(['creator', 'stockMovements'])
            ->withCount('sales')
            ->withSum(['sales' => fn($q) => $q->completed()], 'quantity')
            ->latest()
            ->get();

        $stats = [
            'total_products' => $products->count(),
            'low_stock' => $products->where('is_active', true)->filter(fn($p) => $p->isLowStock())->count(),
            'out_of_stock' => $products->where('current_stock', 0)->count(),
            'total_units' => $products->sum('current_stock'),
        ];

        return view('reports.inventory', compact('products', 'stats'));
    }

    // PDF Exports
    public function exportSalesPdf(Request $request)
    {
        $from = $request->from ? \Carbon\Carbon::parse($request->from) : now()->startOfMonth();
        $to = $request->to ? \Carbon\Carbon::parse($request->to) : now()->endOfMonth();

        $sales = Sale::with(['product', 'staff'])
            ->completed()
            ->whereBetween('sold_at', [$from->startOfDay(), $to->endOfDay()])
            ->latest('sold_at')
            ->get();

        $total = $sales->sum('total_amount');

        $pdf = Pdf::loadView('reports.exports.sales-pdf', compact('sales', 'total', 'from', 'to'))
            ->setPaper('a4', 'landscape');

        return $pdf->download("sales-report-{$from->format('Y-m-d')}-to-{$to->format('Y-m-d')}.pdf");
    }

    public function exportSalesCsv(Request $request)
    {
        $from = $request->from ? \Carbon\Carbon::parse($request->from) : now()->startOfMonth();
        $to = $request->to ? \Carbon\Carbon::parse($request->to) : now()->endOfMonth();

        $sales = Sale::with(['product', 'staff'])
            ->completed()
            ->whereBetween('sold_at', [$from->startOfDay(), $to->endOfDay()])
            ->latest('sold_at')
            ->get();

        $filename = "sales-{$from->format('Y-m-d')}-to-{$to->format('Y-m-d')}.csv";
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"{$filename}\""];

        $callback = function () use ($sales) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Sale #', 'Date', 'Product', 'Staff', 'Qty', 'Price', 'Total', 'Customer', 'Status']);
            foreach ($sales as $sale) {
                fputcsv($handle, [
                    $sale->sale_number,
                    $sale->sold_at->format('Y-m-d H:i'),
                    $sale->product->name,
                    $sale->staff->name,
                    $sale->quantity,
                    $sale->selling_price,
                    $sale->total_amount,
                    $sale->customer_name ?? '-',
                    $sale->status,
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportAttendancePdf(Request $request)
    {
        $from = $request->from ? \Carbon\Carbon::parse($request->from) : now()->startOfMonth();
        $to = $request->to ? \Carbon\Carbon::parse($request->to) : now()->endOfMonth();

        $allStaff = User::whereIn('role', ['staff', 'manager'])->where('is_active', true)->get();
        $records = Attendance::with('user')->whereBetween('date', [$from, $to])->get();

        $summary = $allStaff->map(function ($staff) use ($records) {
            $staffRecords = $records->where('user_id', $staff->id);
            return [
                'staff' => $staff,
                'present' => $staffRecords->whereIn('status', ['present', 'late'])->count(),
                'absent' => $staffRecords->where('status', 'absent')->count(),
                'late' => $staffRecords->where('status', 'late')->count(),
                'leave' => $staffRecords->where('status', 'leave')->count(),
            ];
        });

        $pdf = Pdf::loadView('reports.exports.attendance-pdf', compact('summary', 'from', 'to'))
            ->setPaper('a4');

        return $pdf->download("attendance-report-{$from->format('Y-m-d')}-to-{$to->format('Y-m-d')}.pdf");
    }

    public function exportSalaryPdf(Request $request)
    {
        $from = $request->from ? \Carbon\Carbon::parse($request->from) : now()->startOfMonth();
        $to = $request->to ? \Carbon\Carbon::parse($request->to) : now()->endOfMonth();

        $records = SalaryRecord::with(['employee', 'processor'])
            ->whereBetween('record_date', [$from, $to])
            ->latest('record_date')
            ->get();

        $pdf = Pdf::loadView('reports.exports.salary-pdf', compact('records', 'from', 'to'))
            ->setPaper('a4');

        return $pdf->download("salary-report-{$from->format('Y-m-d')}-to-{$to->format('Y-m-d')}.pdf");
    }
}
