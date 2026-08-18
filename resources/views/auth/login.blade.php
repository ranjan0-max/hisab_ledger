@extends('layouts.guest')

@section('content')
<div class="login-card-container">
    <!-- Left Banner Sidebar (Desktop) -->
    <div class="login-sidebar">
        <div class="d-flex align-items-center gap-3 position-relative" style="z-index: 1;">
            <div class="sidebar-brand-avatar text-white">
                <i class="bi bi-wallet2"></i>
            </div>
            <div>
                <h5 class="fw-extrabold mb-0 text-white" style="font-weight: 850; font-size: 1.15rem;">Hisab Ledger</h5>
                <span class="small" style="color: rgba(255,255,255,0.66); font-size: 0.825rem;">Business, simplified.</span>
            </div>
        </div>

        <div class="position-relative" style="z-index: 1;">
            <h3 class="fw-bold text-white mb-2" style="max-width: 320px; line-height: 1.25; font-size: 1.75rem;">Every account, in one place. Completely clear.</h3>
            <p class="small mb-4" style="color: rgba(255,255,255,0.7); max-width: 320px;">Understand your business position in real time, from daily entries to complete ledgers.</p>
            
            <div class="d-flex flex-column gap-2 mt-4">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill" style="color: #73e0c5; font-size: 1.1rem;"></i>
                    <span class="fw-semibold small">Customer & supplier ledger</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill" style="color: #73e0c5; font-size: 1.1rem;"></i>
                    <span class="fw-semibold small">Client-wise secure access</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill" style="color: #73e0c5; font-size: 1.1rem;"></i>
                    <span class="fw-semibold small">Live receivable analytics</span>
                </div>
            </div>
        </div>

        <div class="small position-relative" style="z-index: 1; color: rgba(255,255,255,0.48); font-size: 0.8rem;">
            Secure • Isolated • Audited
        </div>
    </div>

    <!-- Right Login Form Area -->
    <div class="p-4 p-sm-5 d-flex flex-column justify-content-center">
        <!-- Mobile Brand Header -->
        <div class="d-md-none mb-4">
            <div class="d-flex align-items-center gap-2">
                <div class="p-2 bg-primary text-white rounded-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                    <i class="bi bi-wallet2 fs-5"></i>
                </div>
                <h5 class="fw-bold mb-0">Hisab Ledger</h5>
            </div>
        </div>

        <span class="text-uppercase text-primary fw-extrabold small tracking-wider" style="letter-spacing: 0.12em; font-weight: 850;">Secure Login</span>
        <h2 class="fw-bold text-dark mt-1 mb-1">Welcome back</h2>
        <p class="text-muted small mb-4">Continue to your business dashboard.</p>

        @if (session('error') || request('session_expired'))
            <div class="alert alert-warning border-0 shadow-sm rounded-3 py-2 px-3 mb-4" style="background-color: #fffbeb; color: #92400e; border: 1px solid #fef3c7 !important;">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history text-warning fs-5"></i>
                    <span class="small fw-semibold">{{ session('error') ?? 'Session expired due to inactivity. Please log in again.' }}</span>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-3 py-2 px-3 mb-4" style="background-color: #fef2f2; color: #991b1b; border: 1px solid #fecaca !important;">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-octagon-fill"></i>
                    <span class="small fw-semibold">{{ $errors->first() }}</span>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="d-flex flex-column gap-3">
            @csrf
            
            <div class="position-relative">
                <i class="bi bi-person input-group-icon"></i>
                <input type="text" class="form-control form-control-custom" id="username" name="username" value="{{ old('username') }}" placeholder="Username" required autofocus>
            </div>

            <div class="position-relative">
                <i class="bi bi-lock input-group-icon"></i>
                <input type="password" class="form-control form-control-custom" id="password" name="password" placeholder="Password" required>
            </div>

            <button type="submit" class="btn btn-primary btn-submit-custom text-white w-100 mt-2 d-flex align-items-center justify-content-center gap-2">
                <span>Login to dashboard</span>
                <i class="bi bi-arrow-right"></i>
            </button>
        </form>

        <div class="text-center text-muted small mt-4" style="font-size: 0.775rem;">
            Protected by secure session authentication
        </div>
    </div>
</div>
@endsection
