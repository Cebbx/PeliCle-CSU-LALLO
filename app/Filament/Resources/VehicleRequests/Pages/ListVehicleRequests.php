<?php

namespace App\Filament\Resources\VehicleRequests\Pages;

use App\Filament\Resources\VehicleRequests\VehicleRequestResource;
use App\Models\VehicleRequest;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListVehicleRequests extends ListRecords
{
    protected static string $resource = VehicleRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        VehicleRequest::expirePastPendingRequests();

        return [
            'all' => Tab::make('All'),
            'pending' => Tab::make('Pending')
                ->badge(VehicleRequest::where('status', 'pending')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending')),
            'approved' => Tab::make('Approved')
                ->badge(VehicleRequest::where('status', 'approved')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'approved')),
            'on_trip' => Tab::make('On Trip')
                ->badge(VehicleRequest::where('status', 'on_trip')->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'on_trip')),
            'completed' => Tab::make('Completed')
                ->badge(VehicleRequest::where('status', 'completed')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'completed')),
            'cancelled' => Tab::make('Cancelled')
                ->badge(VehicleRequest::where('status', 'cancelled')->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'cancelled')),
            'rejected' => Tab::make('Rejected')
                ->badge(VehicleRequest::where('status', 'rejected')->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'rejected')),
            'expired' => Tab::make('Expired')
                ->badge(VehicleRequest::where('status', 'expired')->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'expired')),
            'archived' => Tab::make('Archived')
                ->badge(VehicleRequest::onlyTrashed()->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed()),
        ];
    }

    public function mount(): void
    {
        VehicleRequest::expirePastPendingRequests();
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
