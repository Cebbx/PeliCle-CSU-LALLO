<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use App\Filament\Pages\Auth\EmployeeLogin;
use App\Filament\Pages\Auth\AdminLogin;

test('employee can log in with department username cics and password cics', function () {
    $employee = User::updateOrCreate(
        ['email' => 'cics@csu.edu.ph'],
        [
            'name' => 'CICS Department',
            'department' => 'CICS',
            'role' => 'employee',
            'password' => Hash::make('cics'),
            'plain_password' => 'cics',
        ]
    );

    Livewire::test(EmployeeLogin::class)
        ->fillForm([
            'email' => 'cics',
            'password' => 'cics',
        ])
        ->call('authenticate')
        ->assertHasNoFormErrors();

    expect(auth('employee')->check())->toBeTrue();
    expect(auth('employee')->user()->email)->toBe('cics@csu.edu.ph');
});

test('employee can log in with department username cics and password fallback', function () {
    $employee = User::updateOrCreate(
        ['email' => 'cics@csu.edu.ph'],
        [
            'name' => 'CICS Department',
            'department' => 'CICS',
            'role' => 'employee',
            'password' => Hash::make('cics'),
            'plain_password' => 'cics',
        ]
    );

    Livewire::test(EmployeeLogin::class)
        ->fillForm([
            'email' => 'cics',
            'password' => 'password',
        ])
        ->call('authenticate')
        ->assertHasNoFormErrors();

    expect(auth('employee')->check())->toBeTrue();
});

test('employee can log in with full email cics@csu.edu.ph', function () {
    $employee = User::updateOrCreate(
        ['email' => 'cics@csu.edu.ph'],
        [
            'name' => 'CICS Department',
            'department' => 'CICS',
            'role' => 'employee',
            'password' => Hash::make('cics'),
            'plain_password' => 'cics',
        ]
    );

    Livewire::test(EmployeeLogin::class)
        ->fillForm([
            'email' => 'cics@csu.edu.ph',
            'password' => 'cics',
        ])
        ->call('authenticate')
        ->assertHasNoFormErrors();

    expect(auth('employee')->check())->toBeTrue();
});

test('admin can log in with username admin and password', function () {
    $admin = User::updateOrCreate(
        ['email' => 'admin@example.com'],
        [
            'name' => 'System Admin',
            'role' => 'admin',
            'password' => Hash::make('password'),
        ]
    );

    Livewire::test(AdminLogin::class)
        ->fillForm([
            'email' => 'admin',
            'password' => 'password',
        ])
        ->call('authenticate')
        ->assertHasNoFormErrors();

    expect(auth('admin')->check())->toBeTrue();
    expect(auth('admin')->user()->email)->toBe('admin@example.com');
});

test('admin can log in with email admin@example.com', function () {
    $admin = User::updateOrCreate(
        ['email' => 'admin@example.com'],
        [
            'name' => 'System Admin',
            'role' => 'admin',
            'password' => Hash::make('password'),
        ]
    );

    Livewire::test(AdminLogin::class)
        ->fillForm([
            'email' => 'admin@example.com',
            'password' => 'password',
        ])
        ->call('authenticate')
        ->assertHasNoFormErrors();

    expect(auth('admin')->check())->toBeTrue();
});
