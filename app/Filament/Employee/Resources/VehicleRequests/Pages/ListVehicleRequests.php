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
        $userId = auth()->id();
        $defaultTabs = ['pending', 'approved', 'on_trip', 'completed', 'cancelled', 'rejected', 'expired'];

        return [
            CreateAction::make(),
            Action::make('customize_tabs')
                ->label('Filter Tabs (Display)')
                ->icon('heroicon-o-adjustments-horizontal')
                ->color('gray')
                ->modalHeading('Piliin ang mga Tabs na Ipapakita')
                ->modalDescription('I-check o i-uncheck kung aling status tabs ang nais mong lumabas sa navigation bar sa itaas ng table.')
                ->modalSubmitActionLabel('Save Tabs Display')
                ->form([
                    \Filament\Forms\Components\CheckboxList::make('visible_tabs')
                        ->label('Visible Status Tabs')
                        ->options([
                            'pending' => 'Pending',
                            'approved' => 'Approved',
                            'on_trip' => 'On Trip',
                            'completed' => 'Completed',
                            'cancelled' => 'Cancelled',
                            'rejected' => 'Disapproved',
                            'expired' => 'Expired',
                        ])
                        ->default(fn () => cache()->get("employee_visible_tabs_{$userId}") ?? session('employee_visible_tabs', $defaultTabs))
                        ->bulkToggleable()
                        ->columns(2),
                ])
                ->action(function (array $data) use ($userId) {
                    $selected = $data['visible_tabs'] ?? [];
                    cache()->forever("employee_visible_tabs_{$userId}", $selected);
                    session(['employee_visible_tabs' => $selected]);

                    \Filament\Notifications\Notification::make()
                        ->title('Tabs Updated')
                        ->body('Matagumpay na na-update ang mga nakadisplay na status tabs!')
                        ->success()
                        ->send();
                }),
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
        $defaultTabs = ['pending', 'approved', 'on_trip', 'completed', 'cancelled', 'rejected', 'expired'];
        $visible = cache()->get("employee_visible_tabs_{$userId}") ?? session('employee_visible_tabs', $defaultTabs);

        $tabs = [
            'all' => Tab::make('All'),
        ];

        if (in_array('pending', $visible)) {
            $tabs['pending'] = Tab::make('Pending')
                ->badge(VehicleRequest::where('user_id', $userId)->where('status', 'pending')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending'));
        }

        if (in_array('approved', $visible)) {
            $tabs['approved'] = Tab::make('Approved')
                ->badge(VehicleRequest::where('user_id', $userId)->where('status', 'approved')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'approved'));
        }

        if (in_array('on_trip', $visible)) {
            $tabs['on_trip'] = Tab::make('On Trip')
                ->badge(VehicleRequest::where('user_id', $userId)->where('status', 'on_trip')->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'on_trip'));
        }

        if (in_array('completed', $visible)) {
            $tabs['completed'] = Tab::make('Completed')
                ->badge(VehicleRequest::where('user_id', $userId)->where('status', 'completed')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'completed'));
        }

        if (in_array('cancelled', $visible)) {
            $tabs['cancelled'] = Tab::make('Cancelled')
                ->badge(VehicleRequest::where('user_id', $userId)->where('status', 'cancelled')->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'cancelled'));
        }

        if (in_array('rejected', $visible)) {
            $tabs['rejected'] = Tab::make('Disapproved')
                ->badge(VehicleRequest::where('user_id', $userId)->where('status', 'rejected')->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'rejected'));
        }

        if (in_array('expired', $visible)) {
            $tabs['expired'] = Tab::make('Expired')
                ->badge(VehicleRequest::where('user_id', $userId)->where('status', 'expired')->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'expired'));
        }

        return $tabs;
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
