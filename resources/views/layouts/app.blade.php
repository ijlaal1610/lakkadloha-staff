<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="theme-color" content="#4f46e5" />
    <meta name="description" content="Lakkad Loha Staff Management" />
    <title>@yield('title', 'Dashboard') — Lakkad Loha</title>

    <!-- PWA -->
    <link rel="manifest" href="/manifest.json" />
    <link rel="apple-touch-icon" href="/icons/icon-192.png" />

    <!-- Bootstrap 5.3 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Bootstrap Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />

    <link rel="stylesheet" href="/css/app.css" />
    @stack('styles')
</head>
<body>

<!-- Sidebar Overlay (mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <div class="brand-icon">
                <i class="bi bi-building"></i>
            </div>
            <div class="brand-text">
                <span class="brand-name">Lakkad Loha</span>
                <span class="brand-sub">Staff Portal</span>
            </div>
        </div>
        <button class="sidebar-close d-lg-none" id="sidebarClose">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <nav class="sidebar-nav">
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2-fill"></i>
            <span>Dashboard</span>
        </a>

        @if(auth()->user()->isAdminOrManager())
        <div class="nav-section">Inventory</div>
        @endif

        <a href="{{ route('inventory.index') }}" class="nav-item {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
            <i class="bi bi-box-seam-fill"></i>
            <span>Inventory</span>
        </a>

        <div class="nav-section">Sales</div>
        <a href="{{ route('sales.index') }}" class="nav-item {{ request()->routeIs('sales.*') ? 'active' : '' }}">
            <i class="bi bi-cart-fill"></i>
            <span>Sales</span>
        </a>
        <a href="{{ route('sales.create') }}" class="nav-item {{ request()->routeIs('sales.create') ? 'active' : '' }}">
            <i class="bi bi-plus-circle-fill"></i>
            <span>New Sale</span>
        </a>

        <div class="nav-section">Workforce</div>
        <a href="{{ route('attendance.index') }}" class="nav-item {{ request()->routeIs('attendance.*') ? 'active' : '' }}">
            <i class="bi bi-calendar-check-fill"></i>
            <span>Attendance</span>
        </a>
        <a href="{{ route('salary.index') }}" class="nav-item {{ request()->routeIs('salary.*') ? 'active' : '' }}">
            <i class="bi bi-cash-stack"></i>
            <span>Salary</span>
        </a>

        @if(auth()->user()->isAdminOrManager())
        <a href="{{ route('staff.index') }}" class="nav-item {{ request()->routeIs('staff.*') && !request()->routeIs('profile') ? 'active' : '' }}">
            <i class="bi bi-people-fill"></i>
            <span>Staff</span>
        </a>

        <div class="nav-section">Analytics</div>
        <a href="{{ route('reports.index') }}" class="nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-fill"></i>
            <span>Reports</span>
        </a>
        @endif

        @if(auth()->user()->isSuperAdmin())
        <a href="{{ route('audit-logs.index') }}" class="nav-item {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}">
            <i class="bi bi-shield-lock-fill"></i>
            <span>Audit Logs</span>
        </a>
        @endif
    </nav>

    <div class="sidebar-footer">
        <a href="{{ route('profile') }}" class="user-card">
            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="user-avatar" />
            <div class="user-info">
                <span class="user-name">{{ auth()->user()->name }}</span>
                <span class="user-role">{{ auth()->user()->getRoleLabel() }}</span>
            </div>
        </a>
    </div>
</aside>

<!-- Main Content -->
<div class="main-wrapper">
    <!-- Top Navigation -->
    <header class="topnav">
        <div class="topnav-left">
            <button class="btn-icon" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            <nav aria-label="breadcrumb" class="d-none d-sm-block">
                <ol class="breadcrumb mb-0">
                    @yield('breadcrumb')
                </ol>
            </nav>
        </div>
        <div class="topnav-right">
            <!-- Theme Toggle -->
            <button class="btn-icon" id="themeToggle" title="Toggle theme">
                <i class="bi bi-sun-fill" id="themeIcon"></i>
            </button>

            <!-- Notifications -->
            <div class="dropdown">
                <button class="btn-icon position-relative" data-bs-toggle="dropdown" id="notifBtn">
                    <i class="bi bi-bell-fill"></i>
                    <span class="notif-badge" id="notifBadge" style="display:none">0</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end notif-dropdown" id="notifDropdown">
                    <div class="notif-header">
                        <span>Notifications</span>
                        <button class="btn-text" onclick="markAllRead()">Mark all read</button>
                    </div>
                    <div class="notif-list" id="notifList">
                        <div class="notif-empty">
                            <i class="bi bi-bell-slash"></i>
                            <p>No notifications</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Menu -->
            <div class="dropdown">
                <button class="user-btn" data-bs-toggle="dropdown">
                    <img src="{{ auth()->user()->avatar_url }}" alt="Avatar" class="user-avatar-sm" />
                    <span class="d-none d-md-inline">{{ auth()->user()->name }}</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{ route('profile') }}">
                            <i class="bi bi-person me-2"></i>Profile
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Page Content -->
    <main class="page-content">
        <!-- Flash Messages -->
        @if(session('success'))
        <div class="alert-toast alert-toast-success" id="flashSuccess">
            <i class="bi bi-check-circle-fill"></i>
            <span>{{ session('success') }}</span>
            <button onclick="this.parentElement.remove()"><i class="bi bi-x"></i></button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert-toast alert-toast-danger" id="flashError">
            <i class="bi bi-exclamation-circle-fill"></i>
            <span>{{ session('error') }}</span>
            <button onclick="this.parentElement.remove()"><i class="bi bi-x"></i></button>
        </div>
        @endif
        @if($errors->any())
        <div class="alert-toast alert-toast-danger">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>{{ $errors->first() }}</span>
            <button onclick="this.parentElement.remove()"><i class="bi bi-x"></i></button>
        </div>
        @endif

        @yield('content')
    </main>
</div>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.3/chart.umd.min.js"></script>
<script src="/js/app.js"></script>
@stack('scripts')

<script>
// Load notifications
loadNotifications();

function loadNotifications() {
    fetch('/api/v1/notifications', {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        }
    }).then(r => r.json()).then(data => {
        const badge = document.getElementById('notifBadge');
        const list = document.getElementById('notifList');
        const count = data.unread_count ?? 0;

        if (count > 0) {
            badge.style.display = 'flex';
            badge.textContent = count > 9 ? '9+' : count;
        }

        const notifications = data.notifications?.data ?? [];
        if (notifications.length === 0) return;

        list.innerHTML = notifications.map(n => `
            <div class="notif-item ${n.read_at ? '' : 'unread'}" onclick="markRead('${n.id}', this)">
                <div class="notif-icon notif-icon-${n.data.type ?? 'info'}">
                    <i class="bi ${getNotifIcon(n.data.type)}"></i>
                </div>
                <div class="notif-body">
                    <div class="notif-title">${n.data.title ?? 'Notification'}</div>
                    <div class="notif-msg">${n.data.message ?? ''}</div>
                </div>
            </div>
        `).join('');
    }).catch(() => {});
}

function getNotifIcon(type) {
    const icons = { low_stock: 'bi-exclamation-triangle-fill', sale: 'bi-cart-fill', attendance: 'bi-calendar-check-fill' };
    return icons[type] ?? 'bi-bell-fill';
}

function markRead(id, el) {
    el.classList.remove('unread');
    fetch(`/notifications/${id}/read`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    });
}

function markAllRead() {
    document.querySelectorAll('.notif-item.unread').forEach(el => el.classList.remove('unread'));
    document.getElementById('notifBadge').style.display = 'none';
    fetch('/notifications/read-all', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    });
}

// Auto-dismiss flash after 4 seconds
setTimeout(() => {
    document.querySelectorAll('.alert-toast').forEach(el => {
        el.style.animation = 'slideOutRight 0.3s ease forwards';
        setTimeout(() => el.remove(), 300);
    });
}, 4000);
</script>
</body>
</html>
