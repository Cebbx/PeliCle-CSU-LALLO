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
            $user = auth()->user();
            if (!empty($user?->department)) {
                $data['department'] = $user->department;
            } else {
                $email = $user?->email ?? '';
                $prefix = strtolower(explode('@', $email)[0]);
                $validDepts = [
                    'employee' => 'CICS',
                    'admin' => 'Administration Office',
                    'ceo' => 'Office of the CEO',
                    'hrmo' => 'HRMO',
                    'accounting' => 'Accounting Office',
                    'budget' => 'Budget Office',
                    'property' => 'Property and Supply Office',
                    'records' => 'Records Office',
                    'planning' => 'Planning Office',
                    'mis' => 'MIS Office',
                    'registrar' => 'Office of the Campus Registrar',
                    'admission' => 'Campus Admission Office',
                    'publication' => 'Campus Publication Office',
                    'library' => 'University Library',
                    'cics' => 'CICS',
                    'cte' => 'CTE',
                    'chm' => 'CHM',
                    'coa' => 'COA',
                    'cafevalena' => 'Café Valena',
                    'csc' => 'Campus Student Council'
                ];
                $data['department'] = $validDepts[$prefix] ?? 'Campus Student Council';
            }
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



    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
