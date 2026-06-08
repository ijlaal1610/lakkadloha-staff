<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SalaryRecord;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Sales data
        $todaySales = Sale::completed()->today()->sum('total_amount');
        $weeklySales = Sale::completed()->thisWeek()->sum('total_amount');
        $monthlySales = Sale::completed()->thisMonth()->sum('total_amount');
        $yearlySales = Sale::completed()->whereYear('sold_at', now()->year)->sum('total_amount');

        $todaySalesCount = Sale::completed()->today()->count();
        $monthlySalesCount = Sale::completed()->thisMonth()->count();

        // Inventory
        $lowStockProducts = Product::active()->lowStock()->latest()->take(5)->get();
        $totalProducts = Product::active()->count();

        // Attendance
        $presentToday = Attendance::today()->where('status', 'present')->count();
        $absentToday = User::where('role', 'staff')->where('is_active', true)->count()
            - Attendance::today()->count();
        $totalStaff = User::where('is_active', true)->whereIn('role', ['staff', 'manager'])->count();

        // Recent sales
        $recentSales = Sale::with(['product', 'staff'])
            ->completed()
            ->latest()
            ->take(10)
            ->get();

        // Sales chart data (last 7 days)
        $salesChart = Sale::completed()
            ->whereBetween('sold_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()])
            ->select(DB::raw('DATE(sold_at) as date'), DB::raw('SUM(total_amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $chartLabels = [];
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $chartLabels[] = now()->subDays($i)->format('D, d M');
            $chartData[] = $salesChart[$date]->total ?? 0;
        }

        // Monthly sales trend (last 12 months)
        $monthlyTrend = Sale::completed()
            ->whereBetween('sold_at', [now()->subMonths(11)->startOfMonth(), now()->endOfDay()])
            ->select(
                DB::raw("DATE_FORMAT(sold_at, '%Y-%m') as month"),
                DB::raw('SUM(total_amount) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Top products this month
        $topProducts = Sale::completed()
            ->thisMonth()
            ->with('product')
            ->select('product_id', DB::raw('SUM(total_amount) as total'), DB::raw('SUM(quantity) as qty'))
            ->groupBy('product_id')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        // Notifications
        $notifications = $user->notifications()->take(5)->get();
        $unreadCount = $user->unreadNotifications()->count();

$myTodaySales = 0;
$myMonthlySales = 0;
$myAttendanceToday = null;
$mySalaryThisMonth = 0;

        // Staff-specific data
        if ($user->isStaff()) {
            $myTodaySales = Sale::where('staff_id', $user->id)->completed()->today()->sum('total_amount');
            $myMonthlySales = Sale::where('staff_id', $user->id)->completed()->thisMonth()->sum('total_amount');
            $myAttendanceToday = Attendance::where('user_id', $user->id)->today()->first();
            $mySalaryThisMonth = SalaryRecord::where('employee_id', $user->id)
                ->whereMonth('record_date', now()->month)
                ->whereYear('record_date', now()->year)
                ->where('type', 'salary')
                ->sum('amount');
        }

        return view('dashboard.index', compact(
            'user', 'todaySales', 'weeklySales', 'monthlySales', 'yearlySales',
            'todaySalesCount', 'monthlySalesCount', 'lowStockProducts', 'totalProducts',
            'presentToday', 'absentToday', 'totalStaff', 'recentSales',
            'chartLabels', 'chartData', 'monthlyTrend', 'topProducts',
            'notifications', 'unreadCount',
            'myTodaySales', 'myMonthlySales', 'myAttendanceToday', 'mySalaryThisMonth'
        ));
    }

    public function markNotificationRead(Request $request, string $id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return response()->json(['success' => true]);
    }

    public function markAllNotificationsRead(Request $request)
    {
        Auth::user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    }
}
