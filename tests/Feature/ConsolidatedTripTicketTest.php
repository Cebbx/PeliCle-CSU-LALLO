<?php

use App\Models\Driver;
use App\Models\TripTicket;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('employees can submit requests without selecting a vehicle', function () {
    $user = User::factory()->create(['role' => 'employee', 'department' => 'CICS']);

    $request = VehicleRequest::create([
        'request_number' => 'VR-99001',
        'user_id' => $user->id,
        'employee_name' => 'Prof. Juan Dela Cruz',
        'department' => 'CICS',
        'destination' => 'Region II, Cagayan, Tuguegarao City, Centro 01, Rizal St.',
        'purpose' => 'Regional IT Summit',
        'date' => now()->addDays(2)->format('Y-m-d'),
        'time' => '08:00:00',
        'return_date' => now()->addDays(2)->format('Y-m-d'),
        'return_time' => '17:00:00',
        'number_of_passengers' => 3,
        'passenger_names' => [['name' => 'Juan Dela Cruz'], ['name' => 'Maria Santos'], ['name' => 'Pedro Reyes']],
        'status' => 'pending',
    ]);

    expect($request->vehicle)->toBeNull()
        ->and($request->status)->toBe('pending');
});

test('admin can consolidate multiple department requests into a single trip ticket and sync statuses', function () {
    $user1 = User::factory()->create(['role' => 'employee', 'department' => 'CICS']);
    $user2 = User::factory()->create(['role' => 'employee', 'department' => 'CTE']);
    $user3 = User::factory()->create(['role' => 'employee', 'department' => 'COA']);

    $travelDate = now()->addDays(3)->format('Y-m-d');

    $req1 = VehicleRequest::create([
        'request_number' => 'VR-00101',
        'user_id' => $user1->id,
        'employee_name' => 'Prof. Juan (CICS)',
        'department' => 'CICS',
        'destination' => 'Tuguegarao City',
        'purpose' => 'CICS IT Conference',
        'date' => $travelDate,
        'time' => '08:00:00',
        'number_of_passengers' => 3,
        'passenger_names' => [['name' => 'Juan'], ['name' => 'Maria'], ['name' => 'Pedro']],
        'status' => 'pending',
    ]);

    $req2 = VehicleRequest::create([
        'request_number' => 'VR-00102',
        'user_id' => $user2->id,
        'employee_name' => 'Dr. Ana (CTE)',
        'department' => 'CTE',
        'destination' => 'Tuguegarao City',
        'purpose' => 'CHED Education Forum',
        'date' => $travelDate,
        'time' => '08:00:00',
        'number_of_passengers' => 4,
        'passenger_names' => [['name' => 'Ana'], ['name' => 'Ben'], ['name' => 'Clara'], ['name' => 'Dan']],
        'status' => 'pending',
    ]);

    $req3 = VehicleRequest::create([
        'request_number' => 'VR-00103',
        'user_id' => $user3->id,
        'employee_name' => 'Dean Jose (COA)',
        'department' => 'COA',
        'destination' => 'Tuguegarao City',
        'purpose' => 'DA Agri-Research Meeting',
        'date' => $travelDate,
        'time' => '08:00:00',
        'number_of_passengers' => 2,
        'passenger_names' => [['name' => 'Jose'], ['name' => 'Elena']],
        'status' => 'pending',
    ]);

    $driver = Driver::create([
        'name' => 'Mario Bros',
        'contact_number' => '09171234567',
        'license_number' => 'D01-12-345678',
        'status' => 'available',
    ]);

    $vehicle = Vehicle::create([
        'brand' => 'HIACE VAN',
        'model' => 'Commuter',
        'plate_number' => 'SBA3790',
        'capacity' => 14,
        'status' => 'available',
    ]);

    // Admin creates consolidated trip ticket for all 3 requests
    $ticket = TripTicket::create([
        'ticket_number' => 'TT-99999',
        'vehicle_request_id' => $req1->id,
        'driver_id' => $driver->id,
        'vehicle' => 'HIACE VAN - SBA3790',
        'status' => 'pending',
    ]);

    // Simulate afterCreate hook linking companion requests
    $allIds = [$req1->id, $req2->id, $req3->id];
    foreach ($allIds as $id) {
        VehicleRequest::where('id', $id)->update([
            'trip_ticket_id' => $ticket->id,
            'vehicle' => $ticket->vehicle,
            'status' => 'approved',
        ]);
    }

    expect($ticket->vehicleRequests()->count())->toBe(3)
        ->and($ticket->all_vehicle_requests->count())->toBe(3)
        ->and($req2->fresh()->trip_ticket_id)->toBe($ticket->id)
        ->and($req2->fresh()->vehicle)->toBe('HIACE VAN - SBA3790')
        ->and($req3->fresh()->trip_ticket_id)->toBe($ticket->id);

    // When ticket becomes active (on trip), all 3 requests become on_trip
    $ticket->update(['status' => 'active']);

    expect($req1->fresh()->status)->toBe('on_trip')
        ->and($req2->fresh()->status)->toBe('on_trip')
        ->and($req3->fresh()->status)->toBe('on_trip');

    // When ticket is completed (driver/guard scans QR), all 3 requests complete
    $ticket->update(['status' => 'completed']);

    expect($req1->fresh()->status)->toBe('completed')
        ->and($req2->fresh()->status)->toBe('completed')
        ->and($req3->fresh()->status)->toBe('completed');

    // Print view test: ensure authenticated print view renders without errors
    $response = test()->actingAs($user1)->get(route('trip-tickets.print', $ticket->id));
    $response->assertStatus(200);
    $response->assertSee('CICS (3 persons)');
    $response->assertSee('CTE (4 persons)');
    $response->assertSee('COA (2 persons)');
});

test('admin can access edit vehicle request page and fields are properly unlocked', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'employee', 'department' => 'Records Office']);

    $req = VehicleRequest::create([
        'request_number' => '2026-10-01',
        'user_id' => $user->id,
        'employee_name' => 'Marsden Herrera',
        'department' => 'Records Office',
        'destination' => 'Quirino (Angkaki), Ilocos Sur',
        'purpose' => 'Official Business',
        'date' => '2026-10-15',
        'time' => '12:29:00',
        'return_date' => '2026-10-15',
        'return_time' => '20:33:00',
        'number_of_passengers' => 1,
        'passenger_names' => [['name' => 'Lydia Spears']],
        'status' => 'pending',
    ]);

    $response = test()->actingAs($admin, 'admin')->get("/admin/vehicle-requests/{$req->id}/edit");
    $response->assertStatus(200);
    $response->assertSee('Edit Vehicle Request: VR-2026-10-01');
    $response->assertSee('Approve &amp; Create Trip Ticket', false);
});

test('admin can access edit trip ticket page and fields are properly unlocked', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'employee']);
    $driver = Driver::create([
        'name' => 'Driver Dan',
        'contact_number' => '09170000000',
        'license_number' => 'D01-99-999999',
        'status' => 'available',
    ]);
    $vehicle = Vehicle::create([
        'brand' => 'FORTUNER',
        'model' => 'GR-S',
        'plate_number' => 'SBA1749',
        'capacity' => 7,
        'status' => 'available',
    ]);
    $req = VehicleRequest::create([
        'request_number' => '2026-10-02',
        'user_id' => $user->id,
        'employee_name' => 'Test Employee',
        'department' => 'CICS',
        'destination' => 'Tuguegarao City',
        'purpose' => 'Seminar',
        'date' => '2026-10-20',
        'time' => '08:00:00',
        'status' => 'approved',
    ]);
    $ticket = TripTicket::create([
        'ticket_number' => 'TT-2026-10-002',
        'vehicle_request_id' => $req->id,
        'driver_id' => $driver->id,
        'vehicle' => 'FORTUNER - SBA1749',
        'status' => 'pending',
    ]);

    $response = test()->actingAs($admin, 'admin')->get("/admin/trip-tickets/{$ticket->id}/edit");
    $response->assertStatus(200);
    $response->assertSee('Edit Trip Ticket: TT-2026-10-002');
});

