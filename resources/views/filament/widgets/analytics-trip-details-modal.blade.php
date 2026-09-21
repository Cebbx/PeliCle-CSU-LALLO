@php
    $tripTicket = $tripTicket ?? ($record->tripTicket ?? \App\Models\TripTicket::where('vehicle_request_id', $record->id)->first());
    $rawVehicle = !empty($record->vehicle) ? $record->vehicle : ($tripTicket?->vehicle ?? null);
    $vehicleName = \App\Models\Vehicle::getVehicleName($rawVehicle);
    $plateNumber = \App\Models\Vehicle::getPlateNumber($rawVehicle);
    
    $driver = $tripTicket?->driver;

    // Parse passengers safely
    $passengers = [];
    if (is_array($record->passenger_names)) {
        foreach ($record->passenger_names as $p) {
            if (is_array($p)) {
                $passengers[] = $p['name'] ?? reset($p) ?? '';
            } elseif (is_string($p)) {
                $passengers[] = $p;
            }
        }
    } elseif (is_string($record->passenger_names) && !empty($record->passenger_names)) {
        $decoded = json_decode($record->passenger_names, true);
        if (is_array($decoded)) {
            foreach ($decoded as $p) {
                if (is_array($p)) {
                    $passengers[] = $p['name'] ?? reset($p) ?? '';
                } elseif (is_string($p)) {
                    $passengers[] = $p;
                }
            }
        } else {
            $passengers = array_map('trim', explode(',', $record->passenger_names));
        }
    }
    $passengers = array_filter($passengers);

    $badgeColor = match ($record->status) {
        'pending' => 'warning',
        'approved', 'completed' => 'success',
        'on_trip' => 'info',
        'rejected' => 'danger',
        'expired', 'cancelled' => 'gray',
        default => 'gray',
    };

    $statusLabel = match ($record->status) {
        'pending' => 'Pending Approval',
        'approved' => 'Approved / Scheduled',
        'on_trip' => 'Currently On Trip',
        'rejected' => 'Disapproved',
        'completed' => 'Completed',
        'expired' => 'Expired',
        'cancelled' => 'Cancelled',
        default => ucfirst($record->status),
    };
@endphp

<style>
    .trip-modal-wrap {
        display: flex;
        flex-direction: column;
        gap: 12px;
        font-family: inherit;
    }
    .trip-box {
        background-color: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 14px 16px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
    }
    .dark .trip-box {
        background-color: #18181b;
        border-color: #27272a;
    }
    .trip-header-box {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px 16px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    @media (min-width: 640px) {
        .trip-header-box {
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
        }
    }
    .dark .trip-header-box {
        background-color: #18181b;
        border-color: #27272a;
    }
    .trip-grid-2 {
        display: grid;
        grid-template-columns: 1fr;
        gap: 12px;
    }
    @media (min-width: 640px) {
        .trip-grid-2 {
            grid-template-columns: 1fr 1fr;
        }
    }
    .trip-grid-3 {
        display: grid;
        grid-template-columns: 1fr;
        gap: 10px;
    }
    @media (min-width: 640px) {
        .trip-grid-3 {
            grid-template-columns: repeat(3, 1fr);
        }
    }
    .trip-inner-card {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 12px;
    }
    .dark .trip-inner-card {
        background-color: #27272a;
        border-color: #3f3f46;
    }
    .trip-title {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #2563eb;
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 10px;
    }
    .dark .trip-title {
        color: #60a5fa;
    }
    .trip-field {
        margin-bottom: 8px;
    }
    .trip-field:last-child {
        margin-bottom: 0;
    }
    .trip-label {
        font-size: 11px;
        color: #6b7280;
        display: block;
        margin-bottom: 2px;
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }
    .dark .trip-label {
        color: #9ca3af;
    }
    .trip-val {
        font-size: 13px;
        font-weight: 600;
        color: #111827;
        line-height: 1.4;
    }
    .dark .trip-val {
        color: #f3f4f6;
    }
    .trip-val-sub {
        font-size: 11px;
        font-weight: 400;
        color: #6b7280;
    }
    .dark .trip-val-sub {
        color: #9ca3af;
    }
    .trip-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 10px;
        border-radius: 9999px;
        font-size: 11px;
        font-weight: 500;
        background-color: #f1f5f9;
        color: #334155;
        border: 1px solid #e2e8f0;
        margin-right: 4px;
        margin-bottom: 4px;
    }
    .dark .trip-pill {
        background-color: #27272a;
        color: #e2e8f0;
        border-color: #3f3f46;
    }
    .trip-reason {
        padding: 12px 14px;
        border-radius: 10px;
        font-size: 12px;
        line-height: 1.4;
        display: flex;
        align-items: flex-start;
        gap: 8px;
    }
    .trip-reason-danger {
        background-color: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }
    .dark .trip-reason-danger {
        background-color: rgba(153, 27, 27, 0.2);
        border-color: rgba(153, 27, 27, 0.4);
        color: #fca5a5;
    }
    .trip-reason-warn {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #334155;
    }
    .dark .trip-reason-warn {
        background-color: rgba(39, 39, 42, 0.6);
        border-color: #3f3f46;
        color: #cbd5e1;
    }
    .trip-footer-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        padding-top: 14px;
        border-top: 1px solid #e5e7eb;
    }
    .dark .trip-footer-actions {
        border-color: #27272a;
    }
</style>

<div class="trip-modal-wrap">
    <!-- Header Summary Card -->
    <div class="trip-header-box">
        <div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="trip-label" style="margin-bottom: 0;">Request Number</span>
                @if($record->is_urgent)
                    <span style="display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 9999px; font-size: 10px; font-weight: 700; background-color: #fee2e2; color: #b91c1c;">
                        🚨 URGENT
                    </span>
                @endif
            </div>
            <div class="trip-val" style="font-size: 18px; margin-top: 2px;">
                {{ $record->formatted_request_number ?? $record->request_number }}
            </div>
            <div class="trip-val-sub" style="margin-top: 2px;">
                Requested on {{ $record->created_at ? $record->created_at->format('M d, Y · h:i A') : 'N/A' }}
            </div>
        </div>

        <div>
            <x-filament::badge :color="$badgeColor" size="lg">
                {{ $statusLabel }}
            </x-filament::badge>
        </div>
    </div>

    <!-- Reason Box (If Disapproved, Cancelled, or Expired) -->
    @if($record->status === 'rejected' && $record->rejection_reason)
        <div class="trip-reason trip-reason-danger">
            <span style="font-size: 16px; line-height: 1;">⚠️</span>
            <div>
                <strong style="display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em;">Reason for Disapproval:</strong>
                <span style="font-size: 13px;">{{ $record->rejection_reason }}</span>
            </div>
        </div>
    @elseif(in_array($record->status, ['cancelled', 'expired']) && $record->cancellation_reason)
        <div class="trip-reason trip-reason-warn">
            <span style="font-size: 16px; line-height: 1;">ℹ️</span>
            <div>
                <strong style="display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em;">
                    {{ $record->status === 'expired' ? 'Expiration Notice:' : 'Cancellation Reason:' }}
                </strong>
                <span style="font-size: 13px;">{{ $record->cancellation_reason }}</span>
            </div>
        </div>
    @endif

    <!-- Two-Column Grid: Requester & Schedule -->
    <div class="trip-grid-2">
        <!-- Card 1: Requester & Department -->
        <div class="trip-box">
            <div class="trip-title">
                <x-filament::icon icon="heroicon-o-user" style="width: 15px; height: 15px; min-width: 15px;" />
                Requester & Department
            </div>

            <div class="trip-field">
                <span class="trip-label">Requested By</span>
                <span class="trip-val">{{ $record->employee_name }}</span>
            </div>

            <div class="trip-field">
                <span class="trip-label">Office / Department</span>
                <span class="trip-val" style="color: #2563eb;">{{ $record->department ?: 'N/A' }}</span>
            </div>

            <div class="trip-field">
                <span class="trip-label">Total Passengers</span>
                <span class="trip-val">
                    {{ $record->number_of_passengers ?? (count($passengers) ?: 1) }} passenger(s)
                </span>
            </div>
        </div>

        <!-- Card 2: Travel Schedule -->
        <div class="trip-box">
            <div class="trip-title">
                <x-filament::icon icon="heroicon-o-calendar-days" style="width: 15px; height: 15px; min-width: 15px;" />
                Travel Schedule
            </div>

            <div class="trip-field">
                <span class="trip-label">Departure</span>
                <span class="trip-val">
                    {{ $record->date ? \Carbon\Carbon::parse($record->date)->format('M d, Y') : 'N/A' }}
                    @if($record->time)
                        <span class="trip-val-sub">({{ \Carbon\Carbon::parse($record->time)->format('g:i A') }})</span>
                    @endif
                </span>
            </div>

            <div class="trip-field">
                <span class="trip-label">Return</span>
                <span class="trip-val">
                    @if($record->return_date)
                        {{ \Carbon\Carbon::parse($record->return_date)->format('M d, Y') }}
                        @if($record->return_time)
                            <span class="trip-val-sub">({{ \Carbon\Carbon::parse($record->return_time)->format('g:i A') }})</span>
                        @endif
                    @else
                        Same Day Return
                    @endif
                </span>
            </div>

            <div class="trip-field">
                <span class="trip-label">Destination</span>
                <span class="trip-val">{{ $record->destination }}</span>
            </div>
        </div>
    </div>

    <!-- Purpose & Description Box -->
    <div class="trip-box">
        <div class="trip-title">
            <x-filament::icon icon="heroicon-o-document-text" style="width: 15px; height: 15px; min-width: 15px;" />
            Purpose of Travel
        </div>
        <div class="trip-inner-card" style="margin-bottom: @if($record->description) 8px @else 0 @endif;">
            <div class="trip-val" style="font-weight: 500;">
                {{ $record->purpose ?: 'Official Business' }}
            </div>
        </div>
        @if($record->description)
            <div style="margin-top: 6px;">
                <span class="trip-label">Additional Details / Notes:</span>
                <p class="trip-val-sub" style="margin-top: 2px;">{{ $record->description }}</p>
            </div>
        @endif
    </div>

    <!-- Fleet Assignment (Vehicle & Driver) -->
    <div class="trip-box">
        <div class="trip-title">
            <x-filament::icon icon="heroicon-o-truck" style="width: 15px; height: 15px; min-width: 15px;" />
            Assigned Fleet & Driver Dispatch
        </div>

        <div class="trip-grid-3">
            <div class="trip-inner-card">
                <span class="trip-label">Assigned Vehicle</span>
                <div class="trip-val">{{ $vehicleName }}</div>
                @if($plateNumber && $plateNumber !== $vehicleName)
                    <span style="display: inline-block; margin-top: 4px; padding: 1px 6px; font-size: 10px; font-weight: 700; font-family: monospace; border-radius: 4px; background-color: #e2e8f0; color: #1e293b;">
                        {{ $plateNumber }}
                    </span>
                @endif
            </div>

            <div class="trip-inner-card">
                <span class="trip-label">Assigned Driver</span>
                @if($driver)
                    <div class="trip-val">{{ $driver->name }}</div>
                    @if($driver->contact_number)
                        <div class="trip-val-sub" style="margin-top: 3px;">📞 {{ $driver->contact_number }}</div>
                    @endif
                @else
                    <div class="trip-val-sub" style="font-style: italic;">To be assigned</div>
                @endif
            </div>

            <div class="trip-inner-card">
                <span class="trip-label">Trip Ticket</span>
                @if($tripTicket)
                    <div class="trip-val" style="font-family: monospace; color: #10b981;">
                        {{ $tripTicket->ticket_number }}
                    </div>
                    <div class="trip-val-sub" style="text-transform: capitalize; margin-top: 3px;">
                        Status: {{ $tripTicket->status }}
                    </div>
                @else
                    <div class="trip-val-sub" style="font-style: italic;">Not ticketed yet</div>
                @endif
            </div>
        </div>
    </div>

    <!-- Passenger Manifest -->
    @if(count($passengers) > 0 || $record->other_passengers)
        <div class="trip-box">
            <div class="trip-title">
                <x-filament::icon icon="heroicon-o-users" style="width: 15px; height: 15px; min-width: 15px;" />
                Passenger Manifest ({{ count($passengers) }})
            </div>

            @if(count($passengers) > 0)
                <div style="display: flex; flex-wrap: wrap; gap: 6px; padding-top: 4px;">
                    @foreach($passengers as $pName)
                        <span class="trip-pill">
                            👤 {{ $pName }}
                        </span>
                    @endforeach
                </div>
            @endif

            @if($record->other_passengers)
                <div class="trip-val-sub" style="font-style: italic; margin-top: 6px;">
                    Other Passengers: {{ $record->other_passengers }}
                </div>
            @endif
        </div>
    @endif

    <!-- Action Links / Buttons -->
    <div class="trip-footer-actions">
        <x-filament::button
            tag="a"
            href="{{ route('vehicle-requests.print', $record->id) }}"
            target="_blank"
            icon="heroicon-o-printer"
            color="gray"
            size="sm"
            outlined
        >
            Print Requisition Form
        </x-filament::button>

        @if($tripTicket)
            <x-filament::button
                tag="a"
                href="{{ route('trip-tickets.print', $tripTicket->id) }}"
                target="_blank"
                icon="heroicon-o-ticket"
                color="success"
                size="sm"
                outlined
            >
                Print Trip Ticket (QR)
            </x-filament::button>
        @endif

        @if(!empty($record->document))
            <x-filament::button
                tag="a"
                href="{{ route('vehicle-requests.view-signed-document', $record->id) }}"
                target="_blank"
                icon="heroicon-o-document-check"
                color="primary"
                size="sm"
                outlined
            >
                View Signed Document
            </x-filament::button>
        @endif
    </div>
</div>
