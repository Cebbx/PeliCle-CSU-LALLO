<?php

namespace App\Filament\Driver\Resources\TripTickets;

use App\Filament\Driver\Resources\TripTickets\Pages\ListTripTickets;
use App\Filament\Driver\Resources\TripTickets\Pages\ViewTripTicket;
use App\Filament\Driver\Resources\TripTickets\Schemas\TripTicketForm;
use App\Filament\Driver\Resources\TripTickets\Tables\TripTicketsTable;
use App\Models\TripTicket;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TripTicketResource extends Resource
{
    protected static ?string $model = TripTicket::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'ticket_number';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        $user = \Filament\Facades\Filament::auth()->user() ?? auth('driver')->user() ?? auth()->user();
        if ($user && in_array(strtolower($user->role ?? ''), ['admin', 'superadmin'])) {
            return parent::getEloquentQuery();
        }
        $driverId = $user?->driver?->id;
        if (!$driverId && $user) {
            $driver = \App\Models\Driver::where('name', $user->name)
                ->orWhere('license_number', $user->email)
                ->first();
            $driverId = $driver?->id;
        }
        return parent::getEloquentQuery()->where('driver_id', $driverId ?? 0);
    }

    public static function form(Schema $schema): Schema
    {
        return TripTicketForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TripTicketsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTripTickets::route('/'),
            'view' => ViewTripTicket::route('/{record}'),
        ];
    }
}

