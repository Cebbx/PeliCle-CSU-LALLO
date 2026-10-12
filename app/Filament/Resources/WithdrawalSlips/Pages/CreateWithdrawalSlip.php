<?php

namespace App\Filament\Resources\WithdrawalSlips\Pages;

use App\Filament\Concerns\HasBackButton;
use App\Filament\Resources\WithdrawalSlips\WithdrawalSlipResource;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateWithdrawalSlip extends CreateRecord
{
    use HasBackButton;

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

                $this->form->fill(array_merge($this->form->getRawState(), [
                    'trip_ticket_id' => $tripId,
                    'driver_name' => $ticket->driver?->name ?? 'No Driver',
                    'vehicle_name' => $vehicleName,
                    'purpose' => $ticket->vehicleRequest?->purpose ?? 'Official Business',
                ]));
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Withdrawal Slip Created')
            ->body("Control No. {$this->record->slip_number} has been issued successfully.")
            ->success()
            ->actions([
                Action::make('print')
                    ->label('Print Slip')
                    ->icon('heroicon-o-printer')
                    ->url(route('withdrawal-slips.print', $this->record->id))
                    ->openUrlInNewTab(),
            ]);
    }
}
