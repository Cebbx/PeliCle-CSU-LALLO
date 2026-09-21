<x-filament-widgets::widget class="fi-wi-table trip-logs-table-wrapper">
    <style>
        /* Table Layout & Constraints */
        .trip-logs-table-wrapper table {
            width: 100% !important;
            table-layout: auto !important;
        }

        /* 1. Request Number: minimal width, no wrap */
        .trip-logs-table-wrapper .fi-ta-header-cell-request-number,
        .trip-logs-table-wrapper .fi-ta-cell-request-number {
            width: 1% !important;
            white-space: nowrap !important;
        }

        /* 2. Requester: clean width */
        .trip-logs-table-wrapper .fi-ta-header-cell-employee-name,
        .trip-logs-table-wrapper .fi-ta-cell-employee-name {
            white-space: nowrap !important;
            min-width: 130px !important;
        }

        /* 3. Office: guaranteed spacious horizontal line */
        .trip-logs-table-wrapper .fi-ta-header-cell-department,
        .trip-logs-table-wrapper .fi-ta-cell-department {
            white-space: nowrap !important;
            min-width: 180px !important;
        }

        /* 4. Destination: flexible width */
        .trip-logs-table-wrapper .fi-ta-header-cell-destination,
        .trip-logs-table-wrapper .fi-ta-cell-destination {
            min-width: 160px !important;
        }

        /* 5. Travel Date: minimal width, no wrap */
        .trip-logs-table-wrapper .fi-ta-header-cell-date,
        .trip-logs-table-wrapper .fi-ta-cell-date {
            width: 1% !important;
            white-space: nowrap !important;
        }

        /* 6. Vehicle: tightly compacted */
        .trip-logs-table-wrapper .fi-ta-header-cell-vehicle,
        .trip-logs-table-wrapper .fi-ta-cell-vehicle {
            width: 1% !important;
            white-space: nowrap !important;
            padding-left: 6px !important;
            padding-right: 6px !important;
            text-align: center !important;
        }

        /* 7. Status: tightly compacted, placed right next to Vehicle */
        .trip-logs-table-wrapper .fi-ta-header-cell-status,
        .trip-logs-table-wrapper .fi-ta-cell-status {
            width: 1% !important;
            white-space: nowrap !important;
            padding-left: 6px !important;
            padding-right: 6px !important;
            text-align: center !important;
        }

        /* 8. Actions (View): tightly compacted, placed right next to Status */
        .trip-logs-table-wrapper .fi-ta-actions-header-cell,
        .trip-logs-table-wrapper td:has(.fi-ta-actions),
        .trip-logs-table-wrapper .fi-ta-actions-cell {
            width: 1% !important;
            white-space: nowrap !important;
            padding-left: 6px !important;
            padding-right: 14px !important;
        }

        .trip-logs-table-wrapper .fi-ta-actions {
            justify-content: flex-start !important;
            gap: 4px !important;
        }
    </style>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\Widgets\View\WidgetsRenderHook::TABLE_WIDGET_START, scopes: static::class) }}

    {{ $this->table ?? null }}

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\Widgets\View\WidgetsRenderHook::TABLE_WIDGET_END, scopes: static::class) }}
</x-filament-widgets::widget>
