<?php

use Illuminate\Support\Facades\Route;

// Ensure SQLite database file exists if using sqlite
if (config('database.default') === 'sqlite') {
    $dbPath = config('database.connections.sqlite.database');
    if ($dbPath && !file_exists($dbPath) && $dbPath !== ':memory:') {
        @mkdir(dirname($dbPath), 0755, true);
        @touch($dbPath);
    }
}

Route::get('/', function (\Illuminate\Http\Request $request) {
    return view('welcome');
})->name('home');

Route::match(['get', 'post'], '/trip-tickets/{ticket_number}/complete-via-qr', [App\Http\Controllers\QrCodeController::class, 'completeTrip'])->name('trip-tickets.complete-via-qr');
Route::get('/guard/scanner', [App\Http\Controllers\QrCodeController::class, 'scannerPage'])->name('guard.scanner');
Route::post('/guard/verify-pin', [App\Http\Controllers\QrCodeController::class, 'verifyPin'])->name('guard.verify-pin');
Route::post('/guard/verify-otp', [App\Http\Controllers\QrCodeController::class, 'verifyOtp'])->name('guard.verify-otp');
Route::post('/guard/resend-otp', [App\Http\Controllers\QrCodeController::class, 'resendOtp'])->name('guard.resend-otp');
Route::match(['get', 'post'], '/guard/logout', [App\Http\Controllers\QrCodeController::class, 'logout'])->name('guard.logout');
Route::get('/guard/logbook/print', [App\Http\Controllers\QrCodeController::class, 'printGateLogbook'])->name('guard.logbook.print');
Route::get('/admin/backup/download', [App\Http\Controllers\BackupController::class, 'downloadBackup'])->name('admin.backup.download');
Route::get('/debug-500', function () {
    $logPath = storage_path('logs/laravel.log');
    if (!file_exists($logPath)) {
        return 'Log file not found: ' . $logPath;
    }
    $lines = file($logPath);
    return response('<pre style="background:#1e293b;color:#f8fafc;padding:20px;border-radius:8px;font-size:12px;overflow:auto;">' . htmlspecialchars(implode('', array_slice($lines, -100))) . '</pre>');
});

// Hostinger One-Click Setup (Runs storage:link, migrate, and optimize:clear via browser)
Route::get('/hostinger-setup', function () {
    $results = [];
    try {
        \Illuminate\Support\Facades\Artisan::call('storage:link', ['--force' => true]);
        $results[] = '✓ Storage link: SUCCESS';
    } catch (\Throwable $e) {
        $results[] = 'Storage link note: ' . $e->getMessage();
    }

    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        $results[] = '✓ Database Migrations: SUCCESS';
    } catch (\Throwable $e) {
        $results[] = 'Database Migrations: ' . $e->getMessage();
    }

    try {
        (new \Database\Seeders\UserSeeder())->run();
        (new \Database\Seeders\DepartmentUserSeeder())->run();
        (new \Database\Seeders\DriverSeeder())->run();
        (new \Database\Seeders\VehicleSeeder())->run();
        $results[] = '✓ Seed All Accounts (Admin, Departments, Drivers, Vehicles): SUCCESS';
    } catch (\Throwable $e) {
        $results[] = 'User Seed note: ' . $e->getMessage();
    }

    try {
        $admins = \App\Models\User::where('role', 'admin')->pluck('email')->toArray();
        $results[] = '✓ Active Admin Accounts in DB: ' . implode(', ', $admins);
        $empCount = \App\Models\User::whereIn('role', ['employee', 'staff'])->count();
        $results[] = '✓ Active Employees in DB: ' . $empCount;
        $driverCount = \App\Models\User::where('role', 'driver')->count();
        $results[] = '✓ Active Drivers in DB: ' . $driverCount;
    } catch (\Throwable $e) {
        $results[] = 'User query note: ' . $e->getMessage();
    }

    try {
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        $results[] = '✓ Cache Cleared: SUCCESS';
    } catch (\Throwable $e) {
        $results[] = 'Optimize note: ' . $e->getMessage();
    }

    return response('<div style="font-family:sans-serif;padding:30px;line-height:1.8;"><h2>Hostinger Setup Status</h2><pre style="background:#f1f5f9;padding:15px;border-radius:8px;">' . implode("\n", $results) . '</pre><p><a href="/" style="display:inline-block;padding:10px 20px;background:#0284c7;color:#fff;text-decoration:none;border-radius:6px;font-weight:bold;">Go to Application &rarr;</a></p></div>');
});


Route::middleware(['auth:web,admin,employee,driver'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('post','livewire.post.index')->name('post.index');
    
    // Protected Print & Download Routes
    Route::get('/vehicle-requests/{id}/print', [App\Http\Controllers\PrintController::class, 'printRequest'])->name('vehicle-requests.print');
    Route::get('/vehicle-requests/{id}/view-signed-document', [App\Http\Controllers\PrintController::class, 'viewSignedDocument'])->name('vehicle-requests.view-signed-document');
    Route::get('/vehicle-requests/{id}/download-signed-document', [App\Http\Controllers\PrintController::class, 'downloadSignedDocument'])->name('vehicle-requests.download-signed-document');
    Route::get('/trip-tickets/{id}/print', [App\Http\Controllers\PrintController::class, 'printTicket'])->name('trip-tickets.print');
    Route::get('/trip-tickets/{id}/print-travel-order', [App\Http\Controllers\PrintController::class, 'printTravelOrder'])->name('trip-tickets.print-travel-order');
    Route::get('/trip-tickets/{id}/view-signed-document', [App\Http\Controllers\PrintController::class, 'viewTripTicketSignedDocument'])->name('trip-tickets.view-signed-document');
    Route::get('/trip-tickets/{id}/download-signed-document', [App\Http\Controllers\PrintController::class, 'downloadTripTicketSignedDocument'])->name('trip-tickets.download-signed-document');
    Route::get('/withdrawal-slips/{id}/print', [App\Http\Controllers\PrintController::class, 'printSlip'])->name('withdrawal-slips.print');
    Route::get('/analytics/print', [App\Http\Controllers\PrintController::class, 'printAnalyticsReport'])->name('analytics.print');
    Route::get('/logbook/print', [App\Http\Controllers\PrintController::class, 'printTripLogbook'])->name('logbook.print');
    Route::get('/logbook/export-csv', [App\Http\Controllers\PrintController::class, 'exportTripLogbookCsv'])->name('logbook.export-csv');

    // Real-Time Live Notification Check Endpoint (Admin, Driver, and Employee)
    Route::get('/api/admin/live-notifications-check', function (\Illuminate\Http\Request $request) {
        $refererPath = parse_url($request->headers->get('referer', ''), PHP_URL_PATH) ?? '';
        $panel = $request->query('panel');

        if (! $panel) {
            if (str_starts_with($refererPath, '/driver')) {
                $panel = 'driver';
            } elseif (str_starts_with($refererPath, '/employee')) {
                $panel = 'employee';
            } elseif (str_starts_with($refererPath, '/admin')) {
                $panel = 'admin';
            }
        }

        if ($panel && in_array($panel, ['admin', 'driver', 'employee']) && auth($panel)->check()) {
            $user = auth($panel)->user();
        } else {
            $user = auth('admin')->user() 
                ?? auth('driver')->user() 
                ?? auth('employee')->user() 
                ?? auth()->user();
        }

        // GUARD 1: Unauthenticated / Guest
        if (! $user) {
            return response()->json(['count' => 0, 'unread' => []]);
        }

        $unread = $user->unreadNotifications()
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($n) {
                return [
                    'id' => (string) $n->id,
                    'title' => $n->data['title'] ?? 'New System Transaction',
                    'body' => $n->data['body'] ?? '',
                    'is_emergency' => str_contains(strtolower(($n->data['title'] ?? '') . ($n->data['body'] ?? '')), 'emergency') || str_contains(strtolower(($n->data['title'] ?? '') . ($n->data['body'] ?? '')), 'breakdown'),
                    'created_at' => $n->created_at ? $n->created_at->diffForHumans() : 'Just now',
                    'created_at_ts' => $n->created_at ? $n->created_at->timestamp : time(),
                ];
            });

        return response()->json([
            'user_id' => $user->id,
            'role' => $user->role,
            'count' => $user->unreadNotifications()->count(),
            'latest' => $unread->first(),
            'unread' => $unread,
        ]);
    })->name('admin.live-notifications-check');
});

require __DIR__.'/settings.php';
