<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Hisab Ledger') }} - Login</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%234f46e5'><path d='M12.136.326A1.5 1.5 0 0 1 14 1.78V3h.5A1.5 1.5 0 0 1 16 4.5v9a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 13.5v-9a1.5 1.5 0 0 1 1.432-1.499L12.136.326zM5.562 3h6.861L9.654 1.258a.5.5 0 0 0-.766.11l-3.326 1.632zM1 4.5v9a.5.5 0 0 0 .5.5h13a.5.5 0 0 0 .5-.5v-9a.5.5 0 0 0-.5-.5h-13a.5.5 0 0 0-.5.5z'/><path d='M11 7.5a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-2z'/></svg>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 1rem;
            margin: 0;
            overflow-x: hidden;
            position: relative;
            background: linear-gradient(145deg, #f4f5fb 0%, #f8f9fc 48%, #eef8f6 100%);
        }
        body::before {
            content: "";
            position: absolute;
            width: 460px;
            height: 460px;
            border-radius: 50%;
            background-color: rgba(91, 91, 214, 0.09);
            filter: blur(2px);
            top: -260px;
            right: -100px;
            z-index: 0;
        }
        body::after {
            content: "";
            position: absolute;
            width: 360px;
            height: 360px;
            border-radius: 50%;
            background-color: rgba(18, 165, 148, 0.08);
            bottom: -230px;
            left: -100px;
            z-index: 0;
        }
        .login-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 900px;
        }
        .login-card-container {
            min-height: 570px;
            display: grid;
            grid-template-columns: 1fr;
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid rgba(230, 232, 240, 0.9);
            box-shadow: 0 30px 90px rgba(28, 31, 55, 0.13);
            overflow: hidden;
        }
        @media (min-width: 768px) {
            .login-card-container {
                grid-template-columns: 0.95fr 1.05fr;
            }
        }
        .login-sidebar {
            display: none;
            flex-direction: column;
            justify-content: space-between;
            padding: 3rem;
            color: white;
            position: relative;
            overflow: hidden;
            background: linear-gradient(155deg, #292a56 0%, #4c4cb6 68%, #5555c8 100%);
        }
        @media (min-width: 768px) {
            .login-sidebar {
                display: flex;
            }
        }
        .login-sidebar::before {
            content: "";
            position: absolute;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            border: 55px solid rgba(255, 255, 255, 0.045);
            right: -110px;
            top: -90px;
        }
        .login-sidebar::after {
            content: "";
            position: absolute;
            width: 230px;
            height: 230px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.035);
            left: -100px;
            bottom: -110px;
        }
        .sidebar-brand-avatar {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.14);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }
        .form-control-custom {
            height: 48px;
            border-radius: 8px;
            padding-left: 42px;
            border: 1px solid #e2e8f0;
            font-size: 0.95rem;
        }
        .form-control-custom:focus {
            border-color: #4c4cb6;
            box-shadow: 0 0 0 3px rgba(76, 76, 182, 0.15);
        }
        .input-group-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 5;
            color: #94a3b8;
            font-size: 1.1rem;
        }
        .btn-submit-custom {
            height: 48px;
            border-radius: 8px;
            background: #4c4cb6;
            border: none;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }
        .btn-submit-custom:hover {
            background: #3b3b9b;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        @yield('content')
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
