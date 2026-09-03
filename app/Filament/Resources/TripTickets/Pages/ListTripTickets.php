<?php

namespace App\Filament\Resources\TripTickets\Pages;

use App\Filament\Resources\TripTickets\TripTicketResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTripTickets extends ListRecords
{
    protected static string $resource = TripTicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => \Filament\Schemas\Components\Tabs\Tab::make('All'),
            'active' => \Filament\Schemas\Components\Tabs\Tab::make('On Trip')
                ->badge(\App\Models\TripTicket::where('status', 'active')->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('status', 'active')),
            'pending' => \Filament\Schemas\Components\Tabs\Tab::make('Pending')
                ->badge(\App\Models\TripTicket::where('status', 'pending')->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('status', 'pending')),
            'completed' => \Filament\Schemas\Components\Tabs\Tab::make('Completed')
                ->badge(\App\Models\TripTicket::where('status', 'completed')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('status', 'completed')),
            'cancelled' => \Filament\Schemas\Components\Tabs\Tab::make('Cancelled')
                ->badge(\App\Models\TripTicket::where('status', 'cancelled')->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('status', 'cancelled')),
        ];
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
