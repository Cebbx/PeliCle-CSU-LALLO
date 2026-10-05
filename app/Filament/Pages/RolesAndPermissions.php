<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class RolesAndPermissions extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected string $view = 'filament.pages.roles-and-permissions';

    protected static ?string $navigationLabel = 'Roles & Permissions';

    protected static ?string $title = 'Roles & Permissions';

    protected static ?int $navigationSort = 11;

    protected static string|\UnitEnum|null $navigationGroup = 'Settings & Management';

    public function getViewData(): array
    {
        $adminCount = \App\Models\User::where('role', 'admin')->count();
        $employeeCount = \App\Models\User::whereIn('role', ['employee', 'staff'])->count();
        $driverCount = \App\Models\User::where('role', 'driver')->count();
        $guardUserCount = \App\Models\User::where('role', 'guard')->count();
        $guardCount = $guardUserCount > 0 ? $guardUserCount : 3;
        $totalUsers = \App\Models\User::count();

        return [
            'adminCount' => $adminCount,
            'employeeCount' => $employeeCount,
            'driverCount' => $driverCount,
            'guardCount' => $guardCount,
            'totalUsers' => $totalUsers,
        ];
    }
}
