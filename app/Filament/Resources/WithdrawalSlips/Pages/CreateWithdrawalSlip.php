<?php

namespace App\Filament\Resources\WithdrawalSlips\Pages;

use App\Filament\Resources\WithdrawalSlips\WithdrawalSlipResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWithdrawalSlip extends CreateRecord
{
    protected static string $resource = WithdrawalSlipResource::class;

    public function canCreateAnother(): bool
    {
        return false;
    }

    public function mount(): void
    {
        parent::mount();

        $tripId = request()->query('trip_ticket_id');
        if ($tripId) {
            $ticket = \App\Models\TripTicket::with(['driver', 'vehicleRequest'])->find($tripId);
            if ($ticket) {
                $dbVehicle = \App\Models\Vehicle::where('plate_number', $ticket->vehicle)->first();
                $vehicleName = $dbVehicle ? "{$dbVehicle->brand} ({$dbVehicle->plate_number})" : $ticket->vehicle;

                $this->form->fill([
                    'trip_ticket_id' => $tripId,
                    'driver_name' => $ticket->driver?->name ?? 'No Driver',
                    'vehicle_name' => $vehicleName,
                    'destination_address' => $ticket->vehicleRequest?->destination ?? 'No Destination',
                    'purpose' => $ticket->vehicleRequest?->purpose ?? 'Official Business',
                ]);
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return route('withdrawal-slips.print', $this->record->id);
    }
}
