<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\LedgerTransactionController;
use App\Http\Controllers\DailyEntryController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\SlowQueryController;
use App\Http\Controllers\DatabaseBackupController;

// Auth Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/switch-client', [DashboardController::class, 'switchClient'])->name('clients.switch');

    // Client Management Routes
    Route::middleware(['permission:clients.view'])->group(function () {
        Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
    });
    Route::middleware(['permission:clients.manage'])->group(function () {
        Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
        Route::put('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');
    });

    // Roles & Permissions Routes
    Route::middleware(['permission:roles.view'])->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    });
    Route::middleware(['permission:roles.manage'])->group(function () {
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    });

    // User Management Routes
    Route::middleware(['permission:users.view'])->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
    });
    Route::middleware(['permission:users.manage'])->group(function () {
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    });

    // Customers Routes
    Route::middleware(['permission:customers.view'])->group(function () {
        Route::get('/customers', [ContactController::class, 'customers'])->name('customers.index');
        Route::get('/customers/{contact}', [ContactController::class, 'show'])->name('customers.show');
        Route::get('/customers/{contact}/statement/pdf', [ContactController::class, 'statementPdf'])->name('customers.statement.pdf');
    });
    Route::middleware(['permission:customers.manage'])->group(function () {
        Route::post('/customers', [ContactController::class, 'store'])->name('customers.store');
        Route::put('/customers/{contact}', [ContactController::class, 'update'])->name('customers.update');
    });

    // Suppliers Routes
    Route::middleware(['permission:suppliers.view'])->group(function () {
        Route::get('/suppliers', [ContactController::class, 'suppliers'])->name('suppliers.index');
        Route::get('/suppliers/{contact}', [ContactController::class, 'show'])->name('suppliers.show');
        Route::get('/suppliers/{contact}/statement/pdf', [ContactController::class, 'statementPdf'])->name('suppliers.statement.pdf');
    });
    Route::middleware(['permission:suppliers.manage'])->group(function () {
        Route::post('/suppliers', [ContactController::class, 'store'])->name('suppliers.store');
        Route::put('/suppliers/{contact}', [ContactController::class, 'update'])->name('suppliers.update');
    });

    // Transaction Routes
    Route::post('/transactions', [LedgerTransactionController::class, 'store'])->name('transactions.store');
    Route::middleware(['permission:transactions.void'])->group(function () {
        Route::post('/transactions/{transaction}/void', [LedgerTransactionController::class, 'void'])->name('transactions.void');
    });

    // Daily Entries Routes
    Route::middleware(['permission:daily.view'])->group(function () {
        Route::get('/daily', [DailyEntryController::class, 'index'])->name('daily.index');
    });
    Route::middleware(['permission:daily.manage'])->group(function () {
        Route::post('/daily', [DailyEntryController::class, 'store'])->name('daily.store');
        Route::patch('/daily/{dailyEntry}', [DailyEntryController::class, 'update'])->name('daily.update');
        Route::post('/daily/{dailyEntry}/payments', [DailyEntryController::class, 'storePayment'])->name('daily.payments.store');
        Route::post('/daily/{dailyEntry}/void', [DailyEntryController::class, 'void'])->name('daily.void');
    });

    // Audit Logs Routes
    Route::middleware(['permission:audit.view'])->group(function () {
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });

    // Slow Query Routes
    Route::middleware(['permission:slow_query.view'])->group(function () {
        Route::get('/slow-queries', [SlowQueryController::class, 'index'])->name('slow-queries.index');
    });
    Route::middleware(['permission:slow_query.manage'])->group(function () {
        Route::patch('/slow-queries/settings', [SlowQueryController::class, 'updateSettings'])->name('slow-queries.settings');
        Route::delete('/slow-queries/clear-all', [SlowQueryController::class, 'clearAll'])->name('slow-queries.clear-all');
        Route::delete('/slow-queries/{slowQueryLog}', [SlowQueryController::class, 'destroy'])->name('slow-queries.destroy');
    });

    // Database Backups Routes
    Route::middleware(['permission:backup.view'])->group(function () {
        Route::get('/database-backups', [DatabaseBackupController::class, 'index'])->name('database-backups.index');
    });
    Route::middleware(['permission:backup.manage'])->group(function () {
        Route::post('/database-backups', [DatabaseBackupController::class, 'run'])->name('database-backups.run');
        Route::get('/database-backups/google/connect', [DatabaseBackupController::class, 'connectGoogleDrive'])->name('google-drive.connect');
        Route::post('/database-backups/google/disconnect', [DatabaseBackupController::class, 'disconnectGoogleDrive'])->name('google-drive.disconnect');
    });
});
