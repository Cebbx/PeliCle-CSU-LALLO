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

Route::view('/', 'welcome')->name('home');

Route::match(['get', 'post'], '/trip-tickets/{ticket_number}/complete-via-qr', [App\Http\Controllers\QrCodeController::class, 'completeTrip'])->name('trip-tickets.complete-via-qr');
Route::get('/guard/scanner', [App\Http\Controllers\QrCodeController::class, 'scannerPage'])->name('guard.scanner');
Route::post('/guard/verify-pin', [App\Http\Controllers\QrCodeController::class, 'verifyPin'])->name('guard.verify-pin');
Route::post('/guard/verify-otp', [App\Http\Controllers\QrCodeController::class, 'verifyOtp'])->name('guard.verify-otp');
Route::post('/guard/resend-otp', [App\Http\Controllers\QrCodeController::class, 'resendOtp'])->name('guard.resend-otp');
Route::match(['get', 'post'], '/guard/logout', [App\Http\Controllers\QrCodeController::class, 'logout'])->name('guard.logout');
Route::get('/guard/logbook/print', [App\Http\Controllers\QrCodeController::class, 'printGateLogbook'])->name('guard.logbook.print');


Route::middleware(['auth:web,admin,employee,driver'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('post','livewire.post.index')->name('post.index');
    
    // Protected Print & Download Routes
    Route::get('/vehicle-requests/{id}/print', [App\Http\Controllers\PrintController::class, 'printRequest'])->name('vehicle-requests.print');
    Route::get('/vehicle-requests/{id}/view-signed-document', [App\Http\Controllers\PrintController::class, 'viewSignedDocument'])->name('vehicle-requests.view-signed-document');
    Route::get('/vehicle-requests/{id}/download-signed-document', [App\Http\Controllers\PrintController::class, 'downloadSignedDocument'])->name('vehicle-requests.download-signed-document');
    Route::get('/trip-tickets/{id}/print', [App\Http\Controllers\PrintController::class, 'printTicket'])->name('trip-tickets.print');
    Route::get('/trip-tickets/{id}/view-signed-document', [App\Http\Controllers\PrintController::class, 'viewTripTicketSignedDocument'])->name('trip-tickets.view-signed-document');
    Route::get('/trip-tickets/{id}/download-signed-document', [App\Http\Controllers\PrintController::class, 'downloadTripTicketSignedDocument'])->name('trip-tickets.download-signed-document');
    Route::get('/trip-tickets/{id}/print-travel-order', [App\Http\Controllers\PrintController::class, 'printTravelOrder'])->name('trip-tickets.print-travel-order');
    Route::get('/withdrawal-slips/{id}/print', [App\Http\Controllers\PrintController::class, 'printSlip'])->name('withdrawal-slips.print');
    Route::get('/analytics/print', [App\Http\Controllers\PrintController::class, 'printAnalyticsReport'])->name('analytics.print');
    Route::get('/logbook/print', [App\Http\Controllers\PrintController::class, 'printTripLogbook'])->name('logbook.print');
    Route::get('/logbook/export-csv', [App\Http\Controllers\PrintController::class, 'exportTripLogbookCsv'])->name('logbook.export-csv');

    // Admin Real-Time Live Notification Check Endpoint
    Route::get('/api/admin/live-notifications-check', function () {
        $user = auth('admin')->user() ?? auth()->user();
        if (! $user || $user->role !== 'admin') {
            return response()->json(['count' => 0, 'unread' => []]);
        }

        $unread = $user->unreadNotifications()
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($n) {
                return [
                    'id' => $n->id,
                    'title' => $n->data['title'] ?? 'New System Transaction',
                    'body' => $n->data['body'] ?? '',
                    'is_emergency' => str_contains(strtolower(($n->data['title'] ?? '') . ($n->data['body'] ?? '')), 'emergency') || str_contains(strtolower(($n->data['title'] ?? '') . ($n->data['body'] ?? '')), 'breakdown'),
                    'created_at' => $n->created_at ? $n->created_at->diffForHumans() : 'Just now',
                ];
            });

        return response()->json([
            'count' => $user->unreadNotifications()->count(),
            'latest' => $unread->first(),
            'unread' => $unread,
        ]);
    })->name('admin.live-notifications-check');
});

require __DIR__.'/settings.php';
