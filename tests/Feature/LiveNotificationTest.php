<?php

use App\Models\Driver;
use App\Models\TripTicket;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleRequest;
use App\Models\WithdrawalSlip;
use Filament\Notifications\Notification;

test('live notification api returns unread notifications for admin, driver, and employee', function () {
    // 1. Admin
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin_notif_' . uniqid() . '@csu.edu.ph',
    ]);
    Notification::make()
        ->title('🚨 Test Admin Notification')
        ->body('Admin test body')
        ->sendToDatabase($admin);

    $this->actingAs($admin, 'admin')
        ->getJson('/api/admin/live-notifications-check?panel=admin')
        ->assertSuccessful()
        ->assertJson([
            'count' => 1,
            'role' => 'admin',
        ]);

    // 2. Driver (even without active trip)
    $driverUser = User::factory()->create([
        'role' => 'driver',
        'email' => 'driver_notif_' . uniqid() . '@csu.edu.ph',
    ]);
    Notification::make()
        ->title('🚐 Test Driver Notification')
        ->body('Driver test body')
        ->sendToDatabase($driverUser);

    $this->actingAs($driverUser, 'driver')
        ->getJson('/api/admin/live-notifications-check?panel=driver')
        ->assertSuccessful()
        ->assertJson([
            'count' => 1,
            'role' => 'driver',
        ]);

    // 3. Employee
    $employee = User::factory()->create([
        'role' => 'employee',
        'email' => 'emp_notif_' . uniqid() . '@csu.edu.ph',
    ]);
    Notification::make()
        ->title('✅ Test Employee Notification')
        ->body('Employee test body')
        ->sendToDatabase($employee);

    $this->actingAs($employee, 'employee')
        ->getJson('/api/admin/live-notifications-check?panel=employee')
        ->assertSuccessful()
        ->assertJson([
            'count' => 1,
            'role' => 'employee',
        ]);
});

test('transaction trigger: creating vehicle request notifies admins', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin_req_' . uniqid() . '@csu.edu.ph',
    ]);
    $employee = User::factory()->create([
        'role' => 'employee',
        'email' => 'emp_req_' . uniqid() . '@csu.edu.ph',
    ]);

    VehicleRequest::create([
        'user_id' => $employee->id,
        'employee_name' => $employee->name,
        'department' => 'CICS',
        'vehicle' => 'Toyota Hiace - SAA-1234',
        'destination' => 'Tuguegarao City',
        'purpose' => 'Official Meeting',
        'passengers' => 3,
        'date' => now()->addDays(2)->format('Y-m-d'),
        'time' => '08:00:00',
        'status' => 'pending',
    ]);

    expect($admin->unreadNotifications()->count())->toBeGreaterThanOrEqual(1);
});

test('transaction trigger: creating trip ticket notifies employee and driver', function () {
    $employee = User::factory()->create([
        'role' => 'employee',
        'email' => 'emp_tt_' . uniqid() . '@csu.edu.ph',
    ]);
    $driverLicense = 'N01-27-' . rand(100000, 999999);
    $driver = Driver::create([
        'name' => 'Driver TT ' . uniqid(),
        'license_number' => $driverLicense,
        'contact_number' => '09171234567',
        'status' => 'available',
    ]);
    $driverUser = User::factory()->create([
        'role' => 'driver',
        'name' => $driver->name,
        'email' => $driverLicense,
    ]);

    $req = VehicleRequest::create([
        'user_id' => $employee->id,
        'employee_name' => $employee->name,
        'department' => 'CTE',
        'vehicle' => 'Toyota Hiace - SAA-1234',
        'destination' => 'Aparri',
        'purpose' => 'Conference',
        'passengers' => 4,
        'date' => now()->addDays(1)->format('Y-m-d'),
        'time' => '09:00:00',
        'status' => 'approved',
    ]);

    TripTicket::create([
        'ticket_number' => TripTicket::generateNextTicketNumber(),
        'vehicle_request_id' => $req->id,
        'driver_id' => $driver->id,
        'vehicle' => 'Toyota Hiace - SAA-1234',
        'status' => 'pending',
    ]);

    expect($employee->unreadNotifications()->count())->toBeGreaterThanOrEqual(1);
    expect($driverUser->unreadNotifications()->count())->toBeGreaterThanOrEqual(1);
});

test('transaction trigger: issuing withdrawal slip notifies driver', function () {
    $driverLicense = 'N01-27-' . rand(100000, 999999);
    $driver = Driver::create([
        'name' => 'Driver WS ' . uniqid(),
        'license_number' => $driverLicense,
        'contact_number' => '09171234567',
        'status' => 'available',
    ]);
    $driverUser = User::factory()->create([
        'role' => 'driver',
        'name' => $driver->name,
        'email' => $driverLicense,
    ]);

    WithdrawalSlip::create([
        'driver_name' => $driver->name,
        'vehicle_name' => 'Toyota Hiace - SAA-1234',
        'fuel_type' => 'Diesel',
        'requested_liters' => 50,
        'status' => 'approved',
    ]);

    expect($driverUser->unreadNotifications()->count())->toBeGreaterThanOrEqual(1);
});

test('employee request creation is immediately received on admin live notification endpoint', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin_live_' . uniqid() . '@csu.edu.ph',
    ]);
    $employee = User::factory()->create([
        'role' => 'employee',
        'name' => 'Kyla Sloan',
        'department' => 'CICS',
        'email' => 'cics_' . uniqid() . '@csu.edu.ph',
    ]);

    VehicleRequest::create([
        'user_id' => $employee->id,
        'employee_name' => $employee->name,
        'department' => 'CICS',
        'vehicle' => 'Toyota Hiace - SAA-1234',
        'destination' => 'Santa Cruz, Occidental Mindoro',
        'purpose' => 'Official Research Conference',
        'passengers' => 2,
        'date' => now()->addDays(3)->format('Y-m-d'),
        'time' => '08:30:00',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($admin, 'admin')
        ->getJson('/api/admin/live-notifications-check?panel=admin');

    $response->assertSuccessful();
    $data = $response->json();

    expect($data['count'])->toBeGreaterThanOrEqual(1);
    expect($data['latest']['title'])->toContain('New Vehicle Request');
    expect($data['latest']['body'])->toContain('Kyla Sloan');
});
