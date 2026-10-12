<?php

use App\Models\User;
use App\Models\WithdrawalSlip;
use App\Filament\Resources\WithdrawalSlips\Pages\ListWithdrawalSlips;
use Livewire\Livewire;

test('withdrawal slip preview modal view can be rendered', function () {
    $slip = WithdrawalSlip::first() ?? WithdrawalSlip::create([
        'slip_number' => 'WS-2026-TEST',
        'driver_name' => 'Juan Dela Cruz',
        'vehicle_name' => 'Toyota HiAce (ABC 1234)',
        'status' => 'approved',
    ]);

    $view = view('filament.resources.withdrawal-slips.preview-modal', [
        'record' => $slip,
    ])->render();

    expect($view)->toContain($slip->slip_number);
    expect($view)->toContain(route('withdrawal-slips.print', ['id' => $slip->id, 'embed' => 1]));
});

test('withdrawal slip print route renders successfully with and without embed', function () {
    $user = User::factory()->create(['role' => 'admin']);

    $slip = WithdrawalSlip::first() ?? WithdrawalSlip::create([
        'slip_number' => 'WS-2026-TEST-2',
        'driver_name' => 'Juan Dela Cruz',
        'vehicle_name' => 'Toyota HiAce (ABC 1234)',
        'status' => 'approved',
    ]);

    $response = $this->actingAs($user)->get(route('withdrawal-slips.print', $slip->id));
    $response->assertStatus(200);
    $response->assertSee($slip->slip_number);

    $embedResponse = $this->actingAs($user)->get(route('withdrawal-slips.print', ['id' => $slip->id, 'embed' => 1]));
    $embedResponse->assertStatus(200);
    $embedResponse->assertSee($slip->slip_number);
});

test('admin can open view slip modal on withdrawal slips table without exception', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $slip = WithdrawalSlip::first() ?? WithdrawalSlip::create([
        'slip_number' => 'WS-2026-TEST-3',
        'driver_name' => 'Juan Dela Cruz',
        'vehicle_name' => 'Toyota HiAce (ABC 1234)',
        'status' => 'approved',
    ]);

    Livewire::actingAs($admin)
        ->test(ListWithdrawalSlips::class)
        ->assertSuccessful()
        ->callTableAction('view_slip', $slip)
        ->assertHasNoTableActionErrors();
});
