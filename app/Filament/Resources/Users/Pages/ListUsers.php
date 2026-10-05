<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New User Account')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Users')
                ->badge(User::count()),
            'admins' => Tab::make('Admins')
                ->badge(User::where('role', 'admin')->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('role', 'admin')),
            'employees' => Tab::make('Employees & Staff')
                ->badge(User::whereIn('role', ['employee', 'staff'])->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('role', ['employee', 'staff'])),
            'drivers' => Tab::make('Drivers')
                ->badge(User::where('role', 'driver')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('role', 'driver')),
            'guards' => Tab::make('Security Guards')
                ->badge(User::where('role', 'guard')->count())
                ->badgeColor('purple')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('role', 'guard')),
        ];
    }
}
