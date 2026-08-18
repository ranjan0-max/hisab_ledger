<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Hisab Ledger') }}</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%234f46e5'><path d='M12.136.326A1.5 1.5 0 0 1 14 1.78V3h.5A1.5 1.5 0 0 1 16 4.5v9a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 13.5v-9a1.5 1.5 0 0 1 1.432-1.499L12.136.326zM5.562 3h6.861L9.654 1.258a.5.5 0 0 0-.766.11l-3.326 1.632zM1 4.5v9a.5.5 0 0 0 .5.5h13a.5.5 0 0 0 .5-.5v-9a.5.5 0 0 0-.5-.5h-13a.5.5 0 0 0-.5.5z'/><path d='M11 7.5a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-2z'/></svg>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Modern Icons Font -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --sidebar-bg: #0f172a;
            --sidebar-hover: #1e293b;
            --sidebar-active-bg: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
            --body-bg: #f8fafc;
            --card-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -2px rgba(0,0,0,0.05);
            --card-hover-shadow: 0 10px 15px -3px rgba(0,0,0,0.08), 0 4px 6px -4px rgba(0,0,0,0.04);
        }
        body {
            background-color: var(--body-bg);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: #334155;
            -webkit-font-smoothing: antialiased;
        }
        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: var(--sidebar-bg);
            color: #fff;
            box-shadow: 4px 0 10px rgba(0,0,0,0.03);
        }
        .sidebar a, .mobile-nav a {
            color: #94a3b8;
            text-decoration: none;
            padding: 11px 16px;
            display: flex;
            align-items: center;
            border-radius: 8px;
            margin-bottom: 5px;
            font-weight: 500;
            font-size: 0.925rem;
            transition: all 0.2s ease-in-out;
        }
        .sidebar a:hover, .mobile-nav a:hover {
            background: var(--sidebar-hover);
            color: #f8fafc;
            transform: translateX(3px);
        }
        .sidebar a.active, .mobile-nav a.active {
            background: var(--sidebar-active-bg);
            color: #ffffff;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.35);
        }
        .sidebar a i, .mobile-nav a i {
            font-size: 1.1rem;
        }
        .main-content {
            flex: 1;
            padding: 24px;
        }
        .navbar {
            backdrop-filter: blur(8px);
            background-color: rgba(255, 255, 255, 0.9) !important;
            box-shadow: 0 1px 3px 0 rgba(0,0,0,0.05);
        }
        .card {
            border-radius: 12px !important;
            box-shadow: var(--card-shadow);
            transition: all 0.25s ease;
            border: 1px solid rgba(226, 232, 240, 0.8) !important;
        }
        .card:hover {
            box-shadow: var(--card-hover-shadow);
        }
        .btn {
            border-radius: 8px !important;
            font-weight: 500;
            padding: 7px 16px;
            transition: all 0.2s ease;
        }
        .btn-primary {
            background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
            border: none;
            box-shadow: 0 2px 6px rgba(79, 70, 229, 0.3);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #4338ca 0%, #2563eb 100%);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.45);
            transform: translateY(-1px);
        }
        .badge {
            padding: 6px 10px;
            border-radius: 6px;
            font-weight: 600;
            letter-spacing: 0.2px;
        }
        .table thead th {
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            font-weight: 700;
            color: #64748b;
            background-color: #f1f5f9 !important;
            border-bottom: 1px solid #e2e8f0;
            padding: 12px 16px;
        }
        .table tbody td {
            padding: 14px 16px;
            color: #1e293b;
        }
        /* Mobile Bottom Sheet Modal Styling */
        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }
            .main-content {
                padding: 14px 12px;
            }
            .card {
                border-radius: 14px !important;
                margin-bottom: 12px;
            }
            .btn {
                padding: 9px 16px;
            }
            .offcanvas {
                border-top-right-radius: 20px;
                border-bottom-right-radius: 20px;
                background: #0f172a !important;
            }
            .modal-dialog {
                margin: 0 !important;
                position: fixed !important;
                bottom: 0 !important;
                left: 0 !important;
                right: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
            }
            .modal-content {
                border-radius: 20px 20px 0 0 !important;
                border: none !important;
                box-shadow: 0 -10px 25px rgba(0, 0, 0, 0.2) !important;
                animation: slideUpBottom 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            }
            .modal-header {
                position: relative;
                padding-top: 20px;
            }
            .modal-header::before {
                content: '';
                position: absolute;
                top: 8px;
                left: 50%;
                transform: translateX(-50%);
                width: 36px;
                height: 4px;
                background-color: #cbd5e1;
                border-radius: 2px;
            }
        }
        @keyframes slideUpBottom {
            from {
                transform: translateY(100%);
            }
            to {
                transform: translateY(0);
            }
        }
        @keyframes toastCountdown {
            from {
                width: 100%;
            }
            to {
                width: 0%;
            }
        }
        /* Mobile Specific Card Enhancements */
        .mobile-card {
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-left: 4px solid #4f46e5 !important;
            background: #ffffff;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .mobile-card:active {
            transform: scale(0.985);
        }
        .mobile-card-supplier {
            border-left: 4px solid #0ea5e9 !important;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar for Desktop -->
        <div class="sidebar p-3 d-none d-md-block">
            <h4 class="fw-bold mb-4 px-2 text-white d-flex align-items-center gap-2">
                <i class="bi bi-wallet2 text-primary fs-4"></i>
                <span>{{ config('app.name', 'Hisab Ledger') }}</span>
            </h4>
            <nav>
                @php
                    $currentClient = auth()->user()->client;
                @endphp

                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2-fill me-2"></i> {{ $currentClient ? $currentClient->getMenuLabel('dashboard', 'Dashboard') : 'Dashboard' }}
                </a>
                
                @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('clients.view'))
                    <a href="{{ route('clients.index') }}" class="{{ request()->routeIs('clients.*') ? 'active' : '' }}">
                        <i class="bi bi-shop-window me-2"></i> {{ $currentClient ? $currentClient->getMenuLabel('clients', 'Clients / Shops') : 'Clients / Shops' }}
                    </a>
                @endif

                @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('customers.view'))
                    <a href="{{ route('customers.index') }}" class="{{ request()->routeIs('customers.*') ? 'active' : '' }}">
                        <i class="bi bi-person-vcard-fill me-2"></i> {{ $currentClient ? $currentClient->getMenuLabel('customers', 'Customers') : 'Customers' }}
                    </a>
                @endif

                @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('suppliers.view'))
                    <a href="{{ route('suppliers.index') }}" class="{{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                        <i class="bi bi-truck-front-fill me-2"></i> {{ $currentClient ? $currentClient->getMenuLabel('suppliers', 'Suppliers') : 'Suppliers' }}
                    </a>
                @endif

                @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('daily.view'))
                    <a href="{{ route('daily.index') }}" class="{{ request()->routeIs('daily.*') ? 'active' : '' }}">
                        <i class="bi bi-journal-check me-2"></i> {{ $currentClient ? $currentClient->getMenuLabel('daily', 'Daily Entries') : 'Daily Entries' }}
                    </a>
                @endif

                @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('users.view'))
                    <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="bi bi-people-fill me-2"></i> {{ $currentClient ? $currentClient->getMenuLabel('users', 'Users') : 'Users' }}
                    </a>
                @endif

                @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('roles.view'))
                    <a href="{{ route('roles.index') }}" class="{{ request()->routeIs('roles.*') ? 'active' : '' }}">
                        <i class="bi bi-shield-lock-fill me-2"></i> {{ $currentClient ? $currentClient->getMenuLabel('roles', 'Roles & Permissions') : 'Roles & Permissions' }}
                    </a>
                @endif

                @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('audit.view'))
                    <a href="{{ route('audit-logs.index') }}" class="{{ request()->routeIs('audit-logs.*') ? 'active' : '' }}">
                        <i class="bi bi-receipt-cutoff me-2"></i> {{ $currentClient ? $currentClient->getMenuLabel('audit-logs', 'Audit Logs') : 'Audit Logs' }}
                    </a>
                @endif

                @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('slow_query.view'))
                    <a href="{{ route('slow-queries.index') }}" class="{{ request()->routeIs('slow-queries.*') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2 me-2"></i> {{ $currentClient ? $currentClient->getMenuLabel('slow-queries', 'Slow Queries') : 'Slow Queries' }}
                    </a>
                @endif

                @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('backup.view'))
                    <a href="{{ route('database-backups.index') }}" class="{{ request()->routeIs('database-backups.*') ? 'active' : '' }}">
                        <i class="bi bi-cloud-arrow-down-fill me-2"></i> {{ $currentClient ? $currentClient->getMenuLabel('database-backups', 'Database Backups') : 'Database Backups' }}
                    </a>
                @endif
            </nav>
        </div>

        <!-- Main Wrapper -->
        <div class="w-100">
            <!-- Minimalistic Top Navbar -->
            <nav class="navbar border-bottom px-3 py-2 sticky-top bg-white shadow-sm" style="backdrop-filter: blur(12px); background-color: rgba(255, 255, 255, 0.95) !important;">
                <div class="container-fluid p-0 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-sm btn-light border-0 d-md-none rounded-circle d-flex align-items-center justify-content-center text-dark p-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" style="width: 36px; height: 36px; background-color: #f1f5f9;">
                            <i class="bi bi-text-left fs-4"></i>
                        </button>
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold text-dark fs-6 tracking-tight mb-0 d-md-none d-flex align-items-center gap-2">
                                <i class="bi bi-wallet2 text-primary fs-5"></i>
                                <span>{{ config('app.name', 'Hisab Ledger') }}</span>
                            </span>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center gap-2">
                        @if(auth()->user()->isSuperAdmin())
                            <!-- Global Header Shop Switcher for SuperAdmin -->
                            <form method="POST" action="{{ route('clients.switch') }}" class="m-0 d-flex align-items-center">
                                @csrf
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light border-end-0 text-primary rounded-start-pill ps-3">
                                        <i class="bi bi-shop"></i>
                                    </span>
                                    <select name="client_id" class="form-select form-select-sm border-start-0 rounded-end-pill fw-semibold bg-light text-dark shadow-none" onchange="this.form.submit()" style="max-width: 180px; cursor: pointer;">
                                        <option value="">-- All / Select Shop --</option>
                                        @foreach(\App\Models\Client::where('is_active', true)->get() as $cShop)
                                            <option value="{{ $cShop->id }}" {{ session('active_client_id') == $cShop->id ? 'selected' : '' }}>
                                                {{ $cShop->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </form>
                        @endif

                        <!-- Desktop User Info -->
                        <div class="d-none d-md-flex align-items-center bg-light border rounded-pill px-3 py-1">
                            <i class="bi bi-person-circle text-primary me-2 fs-6"></i>
                            <span class="fw-semibold text-dark small me-1">
                                {{ auth()->user()->username }} 
                            </span>
                            @if(auth()->user()->isSuperAdmin())
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle ms-1 py-1 px-2" style="font-size: 10px;">SuperAdmin</span>
                            @endif
                        </div>

                        <!-- Mobile Minimal Profile Avatar -->
                        <div class="d-flex d-md-none align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary fw-bold small border border-primary-subtle" style="width: 34px; height: 34px; font-size: 13px;" title="{{ auth()->user()->username }}">
                            {{ strtoupper(substr(auth()->user()->username, 0, 1)) }}
                        </div>

                        <!-- Logout Button -->
                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 d-flex align-items-center gap-1 shadow-sm" title="Logout">
                                <i class="bi bi-box-arrow-right"></i>
                                <span class="d-none d-sm-inline fw-semibold small">Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </nav>

            <!-- Mobile Offcanvas Drawer Sidebar -->
            <div class="offcanvas offcanvas-start bg-dark text-white" tabindex="-1" id="mobileSidebar" style="max-width: 280px;">
                <div class="offcanvas-header border-bottom border-secondary">
                    <h5 class="offcanvas-title fw-bold d-flex align-items-center gap-2">
                        <i class="bi bi-wallet2 text-primary"></i>
                        <span>{{ config('app.name') }}</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
                </div>
                <div class="offcanvas-body p-2">
                    <nav class="mobile-nav w-100 p-0">
                        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="bi bi-grid-1x2-fill me-2"></i> {{ $currentClient ? $currentClient->getMenuLabel('dashboard', 'Dashboard') : 'Dashboard' }}
                        </a>
                        
                        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('clients.view'))
                            <a href="{{ route('clients.index') }}" class="{{ request()->routeIs('clients.*') ? 'active' : '' }}">
                                <i class="bi bi-shop-window me-2"></i> {{ $currentClient ? $currentClient->getMenuLabel('clients', 'Clients / Shops') : 'Clients / Shops' }}
                            </a>
                        @endif

                        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('customers.view'))
                            <a href="{{ route('customers.index') }}" class="{{ request()->routeIs('customers.*') ? 'active' : '' }}">
                                <i class="bi bi-person-vcard-fill me-2"></i> {{ $currentClient ? $currentClient->getMenuLabel('customers', 'Customers') : 'Customers' }}
                            </a>
                        @endif

                        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('suppliers.view'))
                            <a href="{{ route('suppliers.index') }}" class="{{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                                <i class="bi bi-truck-front-fill me-2"></i> {{ $currentClient ? $currentClient->getMenuLabel('suppliers', 'Suppliers') : 'Suppliers' }}
                            </a>
                        @endif

                        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('daily.view'))
                            <a href="{{ route('daily.index') }}" class="{{ request()->routeIs('daily.*') ? 'active' : '' }}">
                                <i class="bi bi-journal-check me-2"></i> {{ $currentClient ? $currentClient->getMenuLabel('daily', 'Daily Entries') : 'Daily Entries' }}
                            </a>
                        @endif

                        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('users.view'))
                            <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                                <i class="bi bi-people-fill me-2"></i> {{ $currentClient ? $currentClient->getMenuLabel('users', 'Users') : 'Users' }}
                            </a>
                        @endif

                        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('roles.view'))
                            <a href="{{ route('roles.index') }}" class="{{ request()->routeIs('roles.*') ? 'active' : '' }}">
                                <i class="bi bi-shield-lock-fill me-2"></i> {{ $currentClient ? $currentClient->getMenuLabel('roles', 'Roles & Permissions') : 'Roles & Permissions' }}
                            </a>
                        @endif

                        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('audit.view'))
                            <a href="{{ route('audit-logs.index') }}" class="{{ request()->routeIs('audit-logs.*') ? 'active' : '' }}">
                                <i class="bi bi-receipt-cutoff me-2"></i> {{ $currentClient ? $currentClient->getMenuLabel('audit-logs', 'Audit Logs') : 'Audit Logs' }}
                            </a>
                        @endif

                        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('slow_query.view'))
                            <a href="{{ route('slow-queries.index') }}" class="{{ request()->routeIs('slow-queries.*') ? 'active' : '' }}">
                                <i class="bi bi-speedometer2 me-2"></i> {{ $currentClient ? $currentClient->getMenuLabel('slow-queries', 'Slow Queries') : 'Slow Queries' }}
                            </a>
                        @endif

                        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('backup.view'))
                            <a href="{{ route('database-backups.index') }}" class="{{ request()->routeIs('database-backups.*') ? 'active' : '' }}">
                                <i class="bi bi-cloud-arrow-down-fill me-2"></i> {{ $currentClient ? $currentClient->getMenuLabel('backup', 'Database Backups') : 'Database Backups' }}
                            </a>
                        @endif

                        <div class="mt-4 pt-3 border-top border-secondary">
                            <form method="POST" action="{{ route('logout') }}" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center gap-2">
                                    <i class="bi bi-box-arrow-right"></i> Logout
                                </button>
                            </form>
                        </div>
                    </nav>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="main-content">
                <!-- Floating Top-Right Snackbar Container (High z-index to stay above modals) -->
                <div id="appSnackbarContainer" class="position-fixed top-0 end-0 p-3" style="z-index: 1090; max-width: 380px; width: 90%; pointer-events: none;">
                    @if(session('success'))
                        <div class="toast show align-items-center text-white bg-dark border-0 shadow-lg w-100 rounded-3 overflow-hidden mb-2 position-relative" style="pointer-events: auto;" role="alert" aria-live="assertive" aria-atomic="true" id="successToast">
                            <div class="d-flex align-items-center px-3 py-2">
                                <div class="toast-body d-flex align-items-center gap-2 p-0">
                                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                    <span class="fw-medium small">{{ session('success') }}</span>
                                </div>
                                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                            </div>
                            <!-- Countdown Progress Bar -->
                            <div class="toast-progress-bar bg-success" style="height: 3px; width: 100%; animation: toastCountdown 4s linear forwards;"></div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="toast show align-items-center text-white bg-danger border-0 shadow-lg w-100 rounded-3 overflow-hidden mb-2 position-relative" style="pointer-events: auto;" role="alert" aria-live="assertive" aria-atomic="true" id="errorToast">
                            <div class="d-flex align-items-center px-3 py-2">
                                <div class="toast-body d-flex align-items-center gap-2 p-0">
                                    <i class="bi bi-exclamation-triangle-fill text-warning fs-5"></i>
                                    <span class="fw-medium small">{{ session('error') }}</span>
                                </div>
                                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                            </div>
                            <!-- Countdown Progress Bar -->
                            <div class="toast-progress-bar bg-warning" style="height: 3px; width: 100%; animation: toastCountdown 4s linear forwards;"></div>
                        </div>
                    @endif
                </div>

                @yield('content')
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto hide server toasts after 3.5 seconds
            const toasts = document.querySelectorAll('#appSnackbarContainer .toast');
            toasts.forEach(toastEl => {
                setTimeout(() => {
                    const bsToast = bootstrap.Toast.getOrCreateInstance(toastEl);
                    bsToast.hide();
                }, 3500);
            });
        });

        // Global Helper for Client-Side JS Snackbars (Above Modals, Top-Right Position)
        window.showSnackbar = function(message, type = 'success') {
            const container = document.getElementById('appSnackbarContainer');
            if (!container) return;
            
            const toastEl = document.createElement('div');
            toastEl.className = `toast show align-items-center text-white ${type === 'success' ? 'bg-dark' : 'bg-danger'} border-0 shadow-lg w-100 rounded-3 overflow-hidden mb-2 position-relative`;
            toastEl.setAttribute('role', 'alert');
            
            const icon = type === 'success' ? 'bi-check-circle-fill text-success' : 'bi-exclamation-triangle-fill text-warning';
            const barColor = type === 'success' ? 'bg-success' : 'bg-warning';

            toastEl.innerHTML = `
                <div class="d-flex align-items-center px-3 py-2">
                    <div class="toast-body d-flex align-items-center gap-2 p-0">
                        <i class="bi ${icon} fs-5"></i>
                        <span class="fw-medium small">${message}</span>
                    </div>
                    <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-progress-bar ${barColor}" style="height: 3px; width: 100%; animation: toastCountdown 4s linear forwards;"></div>
            `;
            
            container.appendChild(toastEl);
            
            setTimeout(() => {
                const bsToast = bootstrap.Toast.getOrCreateInstance(toastEl);
                bsToast.hide();
                setTimeout(() => toastEl.remove(), 400);
            }, 4000);
        };

        // Global Helper for Export PDF button disabling & progress status
        window.handlePdfExport = function(btnElement, exportUrl) {
            if (btnElement.disabled) return;

            // Store original content
            const originalHtml = btnElement.innerHTML;

            // Disable button and show spinner
            btnElement.disabled = true;
            btnElement.innerHTML = `<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Exporting...`;

            if (window.showSnackbar) {
                window.showSnackbar('PDF Generation started in background. Download will start shortly...', 'success');
            }

            // Trigger file download
            const iframe = document.createElement('iframe');
            iframe.style.display = 'none';
            iframe.src = exportUrl;
            document.body.appendChild(iframe);

            // Re-enable button after 5 seconds to prevent spamming
            setTimeout(() => {
                btnElement.disabled = false;
                btnElement.innerHTML = originalHtml;
                setTimeout(() => iframe.remove(), 2000);
            }, 5000);
        };

        // Automatic Session Expiry & Timeout Redirection Logic
        (function() {
            const sessionLifetimeMinutes = {{ config('session.lifetime', 120) }};
            const sessionLifetimeMs = sessionLifetimeMinutes * 60 * 1000;
            
            // Auto redirect timer when session duration elapses
            setTimeout(function() {
                window.location.href = "{{ route('login') }}?session_expired=1";
            }, sessionLifetimeMs);

            // Global fetch interceptor to redirect immediately on 419 (CSRF/Session Expired) or 401 (Unauthenticated)
            const originalFetch = window.fetch;
            if (originalFetch) {
                window.fetch = async function(...args) {
                    const response = await originalFetch.apply(this, args);
                    if (response.status === 419 || response.status === 401) {
                        window.location.href = "{{ route('login') }}?session_expired=1";
                    }
                    return response;
                };
            }
        })();
    </script>
</body>
</html>
