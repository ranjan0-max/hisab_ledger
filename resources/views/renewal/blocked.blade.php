<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Renewal Payment Due</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { min-height: 100vh; background: #f8fafc; }
        .renewal-backdrop { position: fixed; inset: 0; background: rgba(15, 23, 42, .72); }
        .renewal-dialog { width: min(92vw, 520px); }
    </style>
</head>
<body>
    <div class="renewal-backdrop d-flex align-items-center justify-content-center p-3">
        <div class="renewal-dialog card border-danger shadow-lg text-center">
            <div class="card-body p-4 p-md-5">
                <i class="bi bi-lock-fill text-danger display-3"></i>
                <h2 class="fw-bold mt-3">Server Renewal Payment Due</h2>
                <p class="text-muted mb-2">Access for <strong>{{ $client->name }}</strong> is temporarily blocked.</p>
                <p class="mb-4">
                    Renewal payment was due on
                    <strong>{{ $client->next_renewal_date->format('d M Y') }}</strong>.
                    Please contact the administrator to renew access.
                </p>
                <div class="alert alert-danger mb-4">
                    This dialog cannot be closed until the renewal payment is marked as paid.
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary px-4">
                        <i class="bi bi-box-arrow-right me-1"></i> Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
