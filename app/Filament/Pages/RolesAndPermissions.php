<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class RolesAndPermissions extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static string $view = 'filament.pages.roles-and-permissions';

    protected static ?string $navigationLabel = 'Roles & Permissions';

    protected static ?string $title = 'Roles & Permissions';

    protected static ?int $navigationSort = 11;

    protected static string|\UnitEnum|null $navigationGroup = 'Settings & Management';
}
