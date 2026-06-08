<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('admin', User::class);

        $query = User::withCount(['sales', 'attendances']);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('employee_id', 'like', "%{$request->search}%");
            });
        }

        if ($request->role) $query->where('role', $request->role);
        if ($request->status !== null) $query->where('is_active', $request->status === 'active');

        $staff = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'staff' => User::where('role', 'staff')->count(),
            'managers' => User::where('role', 'manager')->count(),
        ];

        return view('staff.index', compact('staff', 'stats'));
    }

    public function create()
    {
        $this->authorize('admin', User::class);
        return view('staff.create');
    }

    public function store(Request $request)
    {
        $this->authorize('admin', User::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'role' => 'required|in:manager,staff',
            'designation' => 'nullable|string|max:100',
            'joining_date' => 'nullable|date',
            'password' => 'required|string|min:8|confirmed',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        $employeeId = 'LL' . str_pad(User::max('id') + 1, 4, '0', STR_PAD_LEFT);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'role' => $validated['role'],
            'designation' => $validated['designation'],
            'joining_date' => $validated['joining_date'],
            'password' => Hash::make($validated['password']),
            'avatar' => $avatarPath,
            'employee_id' => $employeeId,
            'is_active' => true,
        ]);

        AuditLog::record('created', 'staff', "Created staff member: {$user->name} ({$user->role})", $user);

        return redirect()->route('staff.show', $user)
            ->with('success', "Staff member {$user->name} created with Employee ID: {$employeeId}");
    }

    public function show(User $staff)
    {
        $this->authorize('admin', User::class);
        $staff->load('loginHistories', 'sales', 'attendances', 'salaryRecords.processor');

        $recentSales = $staff->sales()->with('product')->latest('sold_at')->take(10)->get();
        $recentAttendance = $staff->attendances()->latest('date')->take(30)->get();
        $recentSalary = $staff->salaryRecords()->with('processor')->latest('record_date')->take(10)->get();

        $stats = [
            'total_sales' => $staff->sales()->completed()->sum('total_amount'),
            'total_salary_paid' => $staff->salaryRecords()->where('type', 'salary')->sum('amount'),
            'present_days_month' => $staff->attendances()
                ->whereMonth('date', now()->month)
                ->whereIn('status', ['present', 'late'])
                ->count(),
            'sales_this_month' => $staff->sales()->completed()->thisMonth()->sum('total_amount'),
        ];

        return view('staff.show', compact('staff', 'recentSales', 'recentAttendance', 'recentSalary', 'stats'));
    }

    public function edit(User $staff)
    {
        $this->authorize('admin', User::class);
        return view('staff.edit', compact('staff'));
    }

    public function update(Request $request, User $staff)
    {
        $this->authorize('admin', User::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $staff->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'role' => Auth::user()->isSuperAdmin() ? 'required|in:super_admin,manager,staff' : 'required|in:manager,staff',
            'designation' => 'nullable|string|max:100',
            'joining_date' => 'nullable|date',
            'is_active' => 'boolean',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $old = $staff->toArray();

        if ($request->hasFile('avatar')) {
            if ($staff->avatar) Storage::disk('public')->delete($staff->avatar);
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:8|confirmed']);
            $validated['password'] = Hash::make($request->password);
        }

        $staff->update($validated);
        AuditLog::record('updated', 'staff', "Updated staff member: {$staff->name}", $staff, $old, $staff->fresh()->toArray());

        return redirect()->route('staff.show', $staff)->with('success', 'Staff member updated.');
    }

    public function destroy(User $staff)
    {
        $this->authorize('admin', User::class);
        if ($staff->id === Auth::id()) return back()->with('error', 'Cannot delete your own account.');
        AuditLog::record('deleted', 'staff', "Deleted staff member: {$staff->name}");
        $staff->delete();
        return redirect()->route('staff.index')->with('success', 'Staff member removed.');
    }

    public function toggleStatus(User $staff)
    {
        $this->authorize('admin', User::class);
        $staff->update(['is_active' => !$staff->is_active]);
        $status = $staff->is_active ? 'activated' : 'deactivated';
        AuditLog::record($status, 'staff', "Account {$status} for: {$staff->name}");
        return back()->with('success', "Account {$status} successfully.");
    }

    public function loginHistory(User $staff)
    {
        $this->authorize('admin', User::class);
        $history = $staff->loginHistories()->latest('logged_in_at')->paginate(20);
        return view('staff.login-history', compact('staff', 'history'));
    }

    public function profile()
    {
        $user = Auth::user();
        return view('staff.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'avatar' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) Storage::disk('public')->delete($user->avatar);
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        if ($request->filled('current_password')) {
            $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|min:8|confirmed',
            ]);

            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }

            $validated['password'] = Hash::make($request->new_password);
        }

        $user->update($validated);
        return back()->with('success', 'Profile updated successfully.');
    }
}
