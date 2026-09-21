<?php

use App\Models\Driver;
use App\Models\TripTicket;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleRequest;
use App\Models\WithdrawalSlip;

test('system functional test: admin can access all resources and logbook', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin_test_' . uniqid() . '@csu.edu.ph',
    ]);

    $this->actingAs($admin, 'admin')
        ->get('/admin/trip-tickets')
        ->assertSuccessful();

    $this->actingAs($admin, 'admin')
        ->get('/admin/vehicle-requests')
        ->assertSuccessful();

    $this->actingAs($admin, 'admin')
        ->get('/admin/vehicles')
        ->assertSuccessful();

    $this->actingAs($admin, 'admin')
        ->get('/admin/drivers')
        ->assertSuccessful();

    $this->actingAs($admin, 'admin')
        ->get('/admin/withdrawal-slips')
        ->assertSuccessful();

    $this->actingAs($admin, 'admin')
        ->get('/admin/trip-logbook')
        ->assertSuccessful();
});

test('system functional test: employee can access dashboard and vehicle requests', function () {
    $employee = User::factory()->create([
        'role' => 'employee',
        'email' => 'emp_test_' . uniqid() . '@csu.edu.ph',
    ]);

    $this->actingAs($employee, 'employee')
        ->get('/employee')
        ->assertSuccessful();

    $this->actingAs($employee, 'employee')
        ->get('/employee/vehicle-requests')
        ->assertSuccessful();
});

test('system functional test: driver can access driver panel and trip tickets', function () {
    $driverUser = User::factory()->create([
        'role' => 'driver',
        'email' => 'driver_test_' . uniqid() . '@csu.edu.ph',
    ]);

    $this->actingAs($driverUser, 'driver')
        ->get('/driver')
        ->assertSuccessful();

    $this->actingAs($driverUser, 'driver')
        ->get('/driver/trip-tickets')
        ->assertSuccessful();
});

test('system functional test: all print views and reports render without errors', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin_print_' . uniqid() . '@csu.edu.ph',
    ]);

    $driver = Driver::create([
        'name' => 'Juan Dela Cruz',
        'contact_number' => '09123456789',
        'license_number' => 'DL-' . rand(1000, 9999),
        'status' => 'available',
    ]);

    $vehicle = Vehicle::create([
        'brand' => 'Toyota',
        'model' => 'HiAce Grandia',
        'type' => 'Van',
        'plate_number' => 'SAB ' . rand(100, 999),
        'status' => 'available',
    ]);

    $vr = VehicleRequest::create([
        'request_number' => 'VR-2026-09-' . rand(10, 99),
        'user_id' => $admin->id,
        'employee_name' => 'Maria Santos',
        'department' => 'College of Information and Computing Sciences',
        'destination' => 'Tuguegarao City',
        'purpose' => 'Official Business: Regional Conference',
        'date' => '2026-09-14',
        'time' => '07:30',
        'return_date' => '2026-09-14',
        'return_time' => '17:30',
        'status' => 'approved',
    ]);

    $ticket = TripTicket::create([
        'ticket_number' => 'TT-2026-09-' . rand(10, 99),
        'vehicle_request_id' => $vr->id,
        'driver_id' => $driver->id,
        'vehicle' => $vehicle->plate_number,
        'status' => 'scheduled',
    ]);

    $slip = WithdrawalSlip::create([
        'trip_ticket_id' => $ticket->id,
        'status' => 'approved',
    ]);

    // 1. Vehicle Request Print
    $this->actingAs($admin)
        ->get("/vehicle-requests/{$vr->id}/print")
        ->assertSuccessful()
        ->assertSee('Maria Santos');

    // 2. Trip Ticket Print
    $this->actingAs($admin)
        ->get("/trip-tickets/{$ticket->id}/print")
        ->assertSuccessful()
        ->assertSee('Vehicle Trip Ticket');

    // 3. Driver Travel Order Print
    $this->actingAs($admin)
        ->get("/trip-tickets/{$ticket->id}/print-travel-order?type=driver")
        ->assertSuccessful()
        ->assertSee('TRAVEL ORDER')
        ->assertSee('F-OCEO-60105')
        ->assertSee('Juan Dela Cruz');

    // 4. Employee Travel Order Print
    $this->actingAs($admin)
        ->get("/trip-tickets/{$ticket->id}/print-travel-order?type=employee")
        ->assertSuccessful()
        ->assertSee('TRAVEL ORDER')
        ->assertSee('F-OCEO-60105')
        ->assertSee('Maria Santos');

    // 5. Withdrawal Slip Print
    $this->actingAs($admin)
        ->get("/withdrawal-slips/{$slip->id}/print")
        ->assertSuccessful()
        ->assertSee('F - GSO - 61203');

    // 6. Monthly Trip Logbook Print & CSV
    $this->actingAs($admin)
        ->get('/logbook/print?month=2026-09')
        ->assertSuccessful();

    $this->actingAs($admin)
        ->get('/logbook/export-csv?month=2026-09')
        ->assertSuccessful();

    // 7. Gate Logbook Print
    $this->get('/guard/logbook/print?month=2026-09')
        ->assertSuccessful();
});

test('system functional test: guard scanner interface and PIN verification', function () {
    // Scanner view
    $this->get('/guard/scanner')
        ->assertSuccessful()
        ->assertSee('Security Gate Portal');

    // Correct PIN verification
    $this->postJson('/guard/verify-pin', ['pin' => '1234'])
        ->assertSuccessful()
        ->assertJson(['success' => true]);

    // Incorrect PIN verification
    $this->postJson('/guard/verify-pin', ['pin' => '9999'])
        ->assertJson(['success' => false]);
});

test('system functional test: complete trip via QR handles departure and arrival', function () {
    $driver = Driver::create([
        'name' => 'Pedro Penduko',
        'contact_number' => '09123456789',
        'license_number' => 'DL-8888',
        'status' => 'available',
    ]);

    $vehicle = Vehicle::create([
        'brand' => 'Nissan',
        'model' => 'Urvan',
        'type' => 'Van',
        'plate_number' => 'ABC ' . rand(100, 999),
        'status' => 'available',
        'odometer' => 10000,
    ]);

    $user = User::factory()->create();
    $vr = VehicleRequest::create([
        'request_number' => 'VR-2026-09-' . rand(10, 99),
        'user_id' => $user->id,
        'employee_name' => 'Pedro Requester',
        'department' => 'CICS',
        'destination' => 'Aparri',
        'purpose' => 'Inspection',
        'date' => '2026-09-14',
        'time' => '08:00',
        'status' => 'approved',
    ]);

    $ticket = TripTicket::create([
        'ticket_number' => 'TT-QR-' . uniqid(),
        'vehicle_request_id' => $vr->id,
        'driver_id' => $driver->id,
        'vehicle' => $vehicle->plate_number,
        'document' => 'test-ceo-signed.pdf',
        'status' => 'active',
    ]);

    // 1st Access without PIN: Prompts for PIN
    $promptResponse = $this->get("/trip-tickets/{$ticket->ticket_number}/complete-via-qr");
    $promptResponse->assertSuccessful()
        ->assertSee('Security PIN');

    // 2nd Access with valid PIN: Completes trip and releases driver
    $completeResponse = $this->post("/trip-tickets/{$ticket->ticket_number}/complete-via-qr", [
        'pin' => '1234',
    ]);
    $completeResponse->assertSuccessful()
        ->assertSee('Trip Completed');

    $ticket->refresh();
    $driver->refresh();
    expect($ticket->status)->toBe('completed')
        ->and($driver->status)->toBe('available');

    // 3rd Access: Reports already completed
    $alreadyDoneResponse = $this->get("/trip-tickets/{$ticket->ticket_number}/complete-via-qr?pin=1234");
    $alreadyDoneResponse->assertSuccessful()
        ->assertSee('Trip Already Completed');
});
