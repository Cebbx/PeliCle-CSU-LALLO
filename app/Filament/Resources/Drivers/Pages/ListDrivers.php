<?php

namespace App\Filament\Resources\Drivers\Pages;

use App\Filament\Resources\Drivers\DriverResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDrivers extends ListRecords
{
    protected static string $resource = DriverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            \Filament\Actions\Action::make('refresh')
                ->label('Refresh Table')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(fn () => null),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => \Filament\Schemas\Components\Tabs\Tab::make('All'),
            'available' => \Filament\Schemas\Components\Tabs\Tab::make('Available')
                ->badge(\App\Models\Driver::where('status', 'available')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('status', 'available')),
            'on_trip' => \Filament\Schemas\Components\Tabs\Tab::make('On Trip')
                ->badge(\App\Models\Driver::where('status', 'on_trip')->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('status', 'on_trip')),
            'off_duty' => \Filament\Schemas\Components\Tabs\Tab::make('Off Duty')
                ->badge(\App\Models\Driver::whereIn('status', ['off_duty', 'unavailable'])->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->whereIn('status', ['off_duty', 'unavailable'])),
        ];
    }

    public function mount(): void
    {
        parent::mount();

        $tab = request()->query('tab');
        if ($tab && array_key_exists($tab, $this->getCachedTabs())) {
            $this->activeTab = $tab;
        }
    }

    public function getDefaultActiveTab(): string | int | null
    {
        $tab = request()->query('tab');
        if ($tab && array_key_exists($tab, $this->getCachedTabs())) {
            return $tab;
        }
        return parent::getDefaultActiveTab();
    }
}
