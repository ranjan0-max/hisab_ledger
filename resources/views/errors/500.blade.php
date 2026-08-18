@extends('layouts.app')

@section('content')
<div class="container py-5 text-center">
    <div class="card shadow-sm border-0 mx-auto" style="max-width: 500px;">
        <div class="card-body p-5">
            <div class="mb-4">
                <i class="bi bi-exclamation-triangle-fill text-danger display-1"></i>
            </div>
            <h3 class="fw-bold text-dark mb-2">Something Went Wrong</h3>
            <p class="text-muted mb-4">We encountered an unexpected error. Please try again or return to the dashboard.</p>
            <a href="{{ url('/dashboard') }}" class="btn btn-primary px-4 fw-semibold">
                <i class="bi bi-house-door-fill me-1"></i> Back to Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
