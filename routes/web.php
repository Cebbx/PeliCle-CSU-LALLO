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
Route::match(['get', 'post'], '/guard/logout', [App\Http\Controllers\QrCodeController::class, 'logout'])->name('guard.logout');
Route::get('/guard/logbook/print', [App\Http\Controllers\QrCodeController::class, 'printGateLogbook'])->name('guard.logbook.print');


Route::middleware(['auth:web,admin,employee,driver'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('post','livewire.post.index')->name('post.index');
    
    // Protected Print Routes
    Route::get('/vehicle-requests/{id}/print', [App\Http\Controllers\PrintController::class, 'printRequest'])->name('vehicle-requests.print');
    Route::get('/vehicle-requests/{id}/view-signed-document', [App\Http\Controllers\PrintController::class, 'viewSignedDocument'])->name('vehicle-requests.view-signed-document');
    Route::get('/trip-tickets/{id}/print', [App\Http\Controllers\PrintController::class, 'printTicket'])->name('trip-tickets.print');
    Route::get('/trip-tickets/{id}/view-signed-document', [App\Http\Controllers\PrintController::class, 'viewTripTicketSignedDocument'])->name('trip-tickets.view-signed-document');
    Route::get('/trip-tickets/{id}/print-travel-order', [App\Http\Controllers\PrintController::class, 'printTravelOrder'])->name('trip-tickets.print-travel-order');
    Route::get('/withdrawal-slips/{id}/print', [App\Http\Controllers\PrintController::class, 'printSlip'])->name('withdrawal-slips.print');
    Route::get('/analytics/print', [App\Http\Controllers\PrintController::class, 'printAnalyticsReport'])->name('analytics.print');
    Route::get('/logbook/print', [App\Http\Controllers\PrintController::class, 'printTripLogbook'])->name('logbook.print');
    Route::get('/logbook/export-csv', [App\Http\Controllers\PrintController::class, 'exportTripLogbookCsv'])->name('logbook.export-csv');
});

require __DIR__.'/settings.php';
