<?php

namespace App\Filament\Resources\WithdrawalSlips\Pages;

use App\Filament\Resources\WithdrawalSlips\WithdrawalSlipResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditWithdrawalSlip extends EditRecord
{
    protected static string $resource = WithdrawalSlipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Archive Withdrawal Slip')
                ->icon('heroicon-o-archive-box')
                ->color('warning')
                ->modalHeading('Archive Withdrawal Slip')
                ->modalDescription('Are you sure you want to archive this withdrawal slip? It will be moved to archives and can be restored at any time.')
                ->modalSubmitActionLabel('Yes, Archive'),
            RestoreAction::make()
                ->label('Restore Withdrawal Slip')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('success'),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (!empty($data['trip_ticket_id'])) {
            $ticket = \App\Models\TripTicket::with(['driver', 'vehicleRequest'])->find($data['trip_ticket_id']);
            if ($ticket) {
                if (empty($data['driver_name'])) {
                    $data['driver_name'] = $ticket->driver?->name ?? 'No Driver';
                }
                if (empty($data['vehicle_name'])) {
                    $dbVehicle = \App\Models\Vehicle::where('plate_number', $ticket->vehicle)->first();
                    $data['vehicle_name'] = $dbVehicle ? "{$dbVehicle->brand} ({$dbVehicle->plate_number})" : $ticket->vehicle;
                }
                if (empty($data['destination_address'])) {
                    $data['destination_address'] = $ticket->vehicleRequest?->destination ?? 'No Destination';
                }
            }
        }

        if (isset($data['requested_items']) && !is_array($data['requested_items'])) {
            $decoded = json_decode($data['requested_items'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $data['requested_items'] = $decoded;
            } else {
                $data['requested_items'] = [
                    ['item' => 'diesel', 'quantity' => 20]
                ];
            }
        }
        return $data;
    }
}
