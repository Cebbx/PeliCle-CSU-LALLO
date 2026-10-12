<?php

use App\Models\User;
use App\Models\VehicleRequest;
use App\Filament\Pages\Analytics;
use App\Filament\Widgets\BookingsOverTimeChart;
use Livewire\Livewire;

test('admin can access analytics dashboard and widgets without query exceptions', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin_analytics_' . uniqid() . '@csu.edu.ph',
    ]);

    // Create sample request to ensure query filters execute
    VehicleRequest::create([
        'request_number' => 'VR-' . date('Ym') . '-999',
        'employee_name' => 'Test Employee',
        'destination' => 'Tuguegarao City',
        'date' => now()->toDateString(),
        'time' => '08:00:00',
        'status' => 'approved',
        'department' => 'CICS',
        'number_of_passengers' => 4,
    ]);

    $this->actingAs($admin, 'admin')
        ->get('/admin/analytics')
        ->assertSuccessful();

    Livewire::actingAs($admin)
        ->test(Analytics::class)
        ->assertSuccessful();

    Livewire::actingAs($admin)
        ->test(BookingsOverTimeChart::class)
        ->assertSuccessful();
});
