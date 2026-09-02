<?php

namespace App\Filament\Employee\Resources\VehicleRequests\Pages;

use App\Filament\Employee\Resources\VehicleRequests\VehicleRequestResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVehicleRequest extends CreateRecord
{
    protected static string $resource = VehicleRequestResource::class;

    public function canCreateAnother(): bool
    {
        return false;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id() ?? 2;
        $data['status'] = 'pending';

        if (empty($data['employee_name'])) {
            $data['employee_name'] = auth()->user()?->name ?? 'Employee User';
        }

        if (empty($data['department'])) {
            $data['department'] = auth()->user()?->department ?? 'CICS';
        }

        if (empty($data['purpose'])) {
            $data['purpose'] = $data['purpose_select'] ?? 'Official University Travel';
        }

        if (empty($data['request_number']) || \App\Models\VehicleRequest::where('request_number', $data['request_number'])->exists()) {
            $lastRecord = \App\Models\VehicleRequest::latest('id')->first();
            $nextId = $lastRecord ? ($lastRecord->id + 1) : 1;
            $data['request_number'] = 'VR-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        try {
            $admins = \App\Models\User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                \Filament\Notifications\Notification::make()
                    ->title('New Vehicle Request Submitted')
                    ->body("Employee {$this->record->employee_name} submitted {$this->record->request_number} to {$this->record->destination}.")
                    ->icon('heroicon-o-document-text')
                    ->iconColor('warning')
                    ->sendToDatabase($admin);
            }
        } catch (\Throwable $e) {
            // Ignore notification errors
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
