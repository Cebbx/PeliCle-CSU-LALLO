<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('admin can download database backup and guest is unauthorized', function () {
    $guestResponse = $this->get(route('admin.backup.download'));
    $guestResponse->assertStatus(403);

    $admin = User::factory()->create(['role' => 'admin']);
    $response = $this->actingAs($admin, 'admin')->get(route('admin.backup.download'));
    $response->assertOk();
    $response->assertHeader('content-type', 'application/sql');
});