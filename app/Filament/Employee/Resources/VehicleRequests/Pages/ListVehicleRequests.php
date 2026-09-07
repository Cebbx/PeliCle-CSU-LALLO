<?php

namespace App\Filament\Employee\Resources\VehicleRequests\Pages;

use App\Filament\Employee\Resources\VehicleRequests\VehicleRequestResource;
use App\Models\VehicleRequest;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Livewire\Attributes\Url;

class ListVehicleRequests extends ListRecords
{
    protected static string $resource = VehicleRequestResource::class;

    public function getTitle(): string | Htmlable
    {
        return 'Vehicle Requests';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('refresh')
                ->label('Refresh Table')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(fn () => null),
        ];
    }

    protected function getTableQuery(): Builder | Relation | null
    {
        return parent::getTableQuery();
    }

    public function getTabs(): array
    {
        VehicleRequest::expirePastPendingRequests();

        $userId = auth()->id();
        $archivedCount = VehicleRequest::onlyTrashed()->where('user_id', $userId)->count();

        return [
            'all' => Tab::make('All'),
            'pending' => Tab::make('Pending')
                ->badge(VehicleRequest::where('user_id', $userId)->where('status', 'pending')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending')),
            'approved' => Tab::make('Approved')
                ->badge(VehicleRequest::where('user_id', $userId)->where('status', 'approved')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'approved')),
            'on_trip' => Tab::make('On Trip')
                ->badge(VehicleRequest::where('user_id', $userId)->where('status', 'on_trip')->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'on_trip')),
            'completed' => Tab::make('Completed')
                ->badge(VehicleRequest::where('user_id', $userId)->where('status', 'completed')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'completed')),
            'cancelled' => Tab::make('Cancelled')
                ->badge(VehicleRequest::where('user_id', $userId)->where('status', 'cancelled')->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'cancelled')),
            'rejected' => Tab::make('Disapproved')
                ->badge(VehicleRequest::where('user_id', $userId)->where('status', 'rejected')->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'rejected')),
            'expired' => Tab::make('Expired')
                ->badge(VehicleRequest::where('user_id', $userId)->where('status', 'expired')->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'expired')),
            'archived' => Tab::make('Archived')
                ->badge($archivedCount ?: null)
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
