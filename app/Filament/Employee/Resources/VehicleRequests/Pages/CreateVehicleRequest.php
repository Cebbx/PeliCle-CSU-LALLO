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
        $user = \Filament\Facades\Filament::auth()->user() ?? auth('employee')->user() ?? auth()->user();
        $data['user_id'] = $user?->id ?? auth('employee')->id() ?? auth()->id() ?? 2;
        $data['status'] = 'pending';

        if (empty($data['employee_name'])) {
            $name = $user?->name ?? '';
            $deptIndicators = ['College of', 'Office of', 'Department', 'Administration Office', 'Campus', 'Café Valena', 'CICS', 'CTE', 'CHM', 'COA', 'HRMO', 'MIS', 'Employee User'];
            $isDept = false;
            foreach ($deptIndicators as $ind) {
                if (stripos($name, $ind) !== false) {
                    $isDept = true;
                    break;
                }
            }
            $data['employee_name'] = $isDept ? 'Employee Requester' : ($name ?: 'Employee User');
        }

        if (empty($data['department'])) {
            if (!empty($user?->department)) {
                $data['department'] = $user->department;
            } else {
                $name = $user?->name ?? '';
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
                if (isset($validDepts[$prefix])) {
                    $data['department'] = $validDepts[$prefix];
                } else {
                    $found = false;
                    foreach ($validDepts as $key => $deptName) {
                        if (stripos($name, $deptName) !== false || stripos($name, $key) !== false) {
                            $data['department'] = $deptName;
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $data['department'] = 'Campus Student Council';
                    }
                }
            }
        }

        if (empty($data['purpose'])) {
            $data['purpose'] = $data['purpose_select'] ?? 'Official University Travel';
        }

        if (empty($data['request_number']) || \App\Models\VehicleRequest::where('request_number', $data['request_number'])->exists()) {
            $data['request_number'] = \App\Models\VehicleRequest::generateNextRequestNumber();
        }

        return $data;
    }



    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
