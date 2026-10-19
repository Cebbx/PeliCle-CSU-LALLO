<?php

namespace App\Filament\Resources\TripTickets\Pages;

use App\Filament\Concerns\HasBackButton;
use App\Filament\Resources\TripTickets\TripTicketResource;
use App\Models\VehicleRequest;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditTripTicket extends EditRecord
{
    use HasBackButton;

    protected static string $resource = TripTicketResource::class;

    public function getTitle(): string
    {
        return "Edit / Consolidate Trip Ticket: {$this->record->ticket_number}";
    }

    public function mount(int | string $record): void
    {
        parent::mount($record);

        if (!$this->record->canConsolidateOrEdit()) {
            \Filament\Notifications\Notification::make()
                ->title('Edit Not Available')
                ->body('Trip Ticket can only be edited when pending and when a same-date approved & signed compatible destination/route request exists.')
                ->warning()
                ->send();

            $this->redirect($this->getResource()::getUrl('index'));
            return;
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->getBackButtonAction(),
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

        // Unlink any requests that were removed from this ticket (keep them 'approved' since GSO already approved & signed them)
        VehicleRequest::where('trip_ticket_id', $record->id)
            ->whereNotIn('id', $allIds)
            ->update([
                'trip_ticket_id' => null,
                'status' => 'approved',
            ]);

        foreach ($allIds as $reqId) {
            $req = VehicleRequest::find($reqId);
            if ($req) {
                $wasUnassigned = (int) $req->trip_ticket_id !== (int) $record->id;
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

                if ($wasUnassigned && $req->user) {
                    try {
                        $driverName = $record->driver?->name ?? 'Assigned Driver';
                        \Filament\Notifications\Notification::make()
                            ->title('🚐 Request Consolidated into Trip Ticket')
                            ->body("Your request {$req->request_number} to {$req->destination} was approved and consolidated into Trip Ticket {$record->ticket_number} (Driver: {$driverName}, Vehicle: {$record->vehicle}).")
                            ->icon('heroicon-o-check-circle')
                            ->iconColor('success')
                            ->actions([
                                \Filament\Actions\Action::make('view')
                                    ->label('View Status')
                                    ->url('/employee/vehicle-requests'),
                            ])
                            ->sendToDatabase($req->user);
                    } catch (\Throwable $e) {}
                }
            }
        }
    }
}
