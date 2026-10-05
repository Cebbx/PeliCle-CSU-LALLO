<?php

namespace App\Filament\Resources\SecurityGuards;

use App\Filament\Resources\SecurityGuards\Pages\CreateSecurityGuard;
use App\Filament\Resources\SecurityGuards\Pages\EditSecurityGuard;
use App\Filament\Resources\SecurityGuards\Pages\ListSecurityGuards;
use App\Filament\Resources\SecurityGuards\Schemas\SecurityGuardForm;
use App\Filament\Resources\SecurityGuards\Tables\SecurityGuardsTable;
use App\Models\SecurityGuard;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class SecurityGuardResource extends Resource
{
    protected static ?string $model = SecurityGuard::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Security Guards';

    protected static ?string $modelLabel = 'Security Guard';

    protected static ?string $pluralModelLabel = 'Security Guards';

    protected static ?int $navigationSort = 11;

    protected static string|\UnitEnum|null $navigationGroup = 'Settings & Management';

    public static function form(Schema $schema): Schema
    {
        return SecurityGuardForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SecurityGuardsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSecurityGuards::route('/'),
            'create' => CreateSecurityGuard::route('/create'),
            'edit' => EditSecurityGuard::route('/{record}/edit'),
        ];
    }
}
