<?php

namespace App\Filament\Resources\TripTickets\Pages;

use App\Filament\Resources\TripTickets\TripTicketResource;
use App\Models\VehicleRequest;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditTripTicket extends EditRecord
{
    protected static string $resource = TripTicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Archive Trip Ticket')
                ->icon('heroicon-o-archive-box')
                ->color('warning')
                ->modalHeading('Archive Trip Ticket')
                ->modalDescription('Are you sure you want to archive this trip ticket? It will be moved to archives and can be restored at any time.')
                ->modalSubmitActionLabel('Yes, Archive'),
            RestoreAction::make()
                ->label('Restore Trip Ticket')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('success'),
        ];
    }

    protected function afterSave(): void
    {
        $record = $this->record;
        $primaryId = $record->vehicle_request_id;
        $companionIds = $this->data['companion_requests'] ?? [];

        $allIds = array_unique(array_filter(array_merge([$primaryId], is_array($companionIds) ? $companionIds : [])));
        $status = $record->status === 'active' ? 'on_trip' : ($record->status === 'completed' ? 'completed' : 'approved');

        // Unlink any requests that were removed from this ticket
        VehicleRequest::where('trip_ticket_id', $record->id)
            ->whereNotIn('id', $allIds)
            ->update([
                'trip_ticket_id' => null,
                'status' => 'pending',
            ]);

        foreach ($allIds as $reqId) {
            $req = VehicleRequest::find($reqId);
            if ($req) {
                $updateData = [
                    'trip_ticket_id' => $record->id,
                    'status' => $status,
                ];
                if ($record->vehicle) {
                    $updateData['vehicle'] = $record->vehicle;
                }
                if ($record->document && !$req->document) {
                    $updateData['document'] = $record->document;
                }
                $req->updateQuietly($updateData);
            }
        }
    }
}
