<?php

use App\Models\TripTicket;
use App\Models\VehicleRequest;
use App\Models\User;
use Carbon\Carbon;

test('trip ticket numbers follow monthly sequence and reset each month', function () {
    // September date
    $septDate = Carbon::parse('2026-09-09');
    $tt1 = TripTicket::generateNextTicketNumber($septDate);
    expect($tt1)->toBe('TT-2026-09-01');

    $user = User::factory()->create();
    $vrDummy = VehicleRequest::create([
        'request_number' => 'VR-2026-09-99',
        'user_id' => $user->id,
        'employee_name' => 'Initial User',
        'department' => 'CICS',
        'destination' => 'Cotabato City',
        'purpose' => 'Official Travel',
        'date' => '2026-09-09',
        'time' => '08:00',
        'status' => 'pending',
    ]);

    // Create a ticket with that number
    TripTicket::create([
        'ticket_number' => $tt1,
        'vehicle_request_id' => $vrDummy->id,
        'status' => 'pending',
    ]);

    // Next ticket in September should be 02
    $tt2 = TripTicket::generateNextTicketNumber($septDate);
    expect($tt2)->toBe('TT-2026-09-02');

    // In October, sequence should reset back to 01
    $octDate = Carbon::parse('2026-10-01');
    $ttOct = TripTicket::generateNextTicketNumber($octDate);
    expect($ttOct)->toBe('TT-2026-10-01');
});

test('vehicle request numbers follow monthly sequence and reset each month', function () {
    $septDate = Carbon::parse('2026-09-09');
    $vr1 = VehicleRequest::generateNextRequestNumber($septDate);
    expect($vr1)->toBe('2026-09-01');

    $user = User::factory()->create();

    VehicleRequest::create([
        'request_number' => $vr1,
        'user_id' => $user->id,
        'employee_name' => 'Test Employee',
        'department' => 'CICS',
        'destination' => 'Cotabato City',
        'purpose' => 'Official Travel',
        'date' => '2026-09-09',
        'time' => '08:00',
        'status' => 'pending',
    ]);

    $vr2 = VehicleRequest::generateNextRequestNumber($septDate);
    expect($vr2)->toBe('2026-09-02');

    $octDate = Carbon::parse('2026-10-05');
    $vrOct = VehicleRequest::generateNextRequestNumber($octDate);
    expect($vrOct)->toBe('2026-10-01');
});

test('formatted ticket number displays TT No. Lal-lo format', function () {
    $ticket = new TripTicket(['ticket_number' => 'TT-2026-09-09']);
    expect($ticket->formatted_ticket_number)->toBe('TT No. Lal-lo - 2026-09-09');
});

test('authenticated user can view and print monthly trip logbook', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = test()->actingAs($admin)->get(route('logbook.print', [
        'year' => '2026',
        'month' => '09',
    ]));

    $response->assertStatus(200);
    $response->assertSee('MONTHLY VEHICLE TRAVEL LOGBOOK');
    $response->assertSee('SEPTEMBER 2026');
    $response->assertSee('JOEL A. TUMAMAO');
});

test('admin can visit trip logbook page in filament', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    \Livewire\Livewire::actingAs($admin)
        ->test(\App\Filament\Pages\TripLogbook::class)
        ->assertSuccessful()
        ->assertSee('Vehicle Trip Logbook');
});

test('authenticated user can export monthly trip logbook as csv', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = test()->actingAs($admin)->get(route('logbook.export-csv', [
        'year' => '2026',
        'month' => '09',
    ]));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
});
