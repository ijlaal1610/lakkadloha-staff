<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::user()->isSuperAdmin()) abort(403);
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $query = AuditLog::with('user');

        if ($request->search) {
            $query->where('description', 'like', "%{$request->search}%");
        }
        if ($request->module) $query->where('module', $request->module);
        if ($request->user_id) $query->where('user_id', $request->user_id);
        if ($request->date_from) $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->date_to) $query->whereDate('created_at', '<=', $request->date_to);

        $logs = $query->latest()->paginate(25)->withQueryString();

        $modules = AuditLog::distinct()->pluck('module');
        $users = User::where('is_active', true)->orderBy('name')->get();

        return view('admin.audit-logs', compact('logs', 'modules', 'users'));
    }
}
