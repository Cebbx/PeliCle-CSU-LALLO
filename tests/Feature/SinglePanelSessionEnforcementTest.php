<?php

use App\Models\User;

test('authenticated admin cannot access employee or driver login and is bounced to admin', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin_single_' . uniqid() . '@csu.edu.ph',
    ]);

    // 1. Visit employee login while logged in as admin
    $response = $this->actingAs($admin, 'admin')
        ->get('/employee/login');

    $response->assertRedirect('/admin');

    // 2. Visit driver login while logged in as admin
    $responseDriver = $this->actingAs($admin, 'admin')
        ->get('/driver/login');

    $responseDriver->assertRedirect('/admin');
});

test('authenticated employee cannot access admin or driver login and is bounced to employee', function () {
    $employee = User::factory()->create([
        'role' => 'employee',
        'email' => 'emp_single_' . uniqid() . '@csu.edu.ph',
    ]);

    // 1. Visit admin login while logged in as employee
    $response = $this->actingAs($employee, 'employee')
        ->get('/admin/login');

    $response->assertRedirect('/employee');

    // 2. Visit driver login while logged in as employee
    $responseDriver = $this->actingAs($employee, 'employee')
        ->get('/driver/login');

    $responseDriver->assertRedirect('/employee');
});

test('authenticated driver cannot access admin or employee login and is bounced to driver', function () {
    $driverUser = User::factory()->create([
        'role' => 'driver',
        'email' => 'driver_single_' . uniqid() . '@csu.edu.ph',
    ]);

    // 1. Visit admin login while logged in as driver
    $response = $this->actingAs($driverUser, 'driver')
        ->get('/admin/login');

    $response->assertRedirect('/driver');

    // 2. Visit employee login while logged in as driver
    $responseEmployee = $this->actingAs($driverUser, 'driver')
        ->get('/employee/login');

    $responseEmployee->assertRedirect('/driver');
});
