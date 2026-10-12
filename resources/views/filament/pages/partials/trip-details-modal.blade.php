@php
    $allRequests = $record->all_vehicle_requests;
    $primaryReq = $record->vehicleRequest ?? $allRequests->first();
    $isCarpool = $allRequests->count() > 1;

    // Departure Info
    $depDate = $primaryReq?->date ? \Carbon\Carbon::parse($primaryReq->date)->format('F d, Y') : ($record->created_at ? $record->created_at->format('F d, Y') : '---');
    $depTime = $primaryReq?->time ? \Carbon\Carbon::parse($primaryReq->time)->format('g:i A') : '---';

    // Gate Out Timestamp
    $gateOutCarbon = $record->gate_out_at ? \Carbon\Carbon::parse($record->gate_out_at)->timezone('Asia/Manila') : null;
    $gateOutFormatted = $gateOutCarbon ? $gateOutCarbon->format('M d, Y · g:i A') : null;

    // Gate In / Arrival Timestamp
    $gateInCarbon = $record->gate_in_at ? \Carbon\Carbon::parse($record->gate_in_at)->timezone('Asia/Manila') : ($record->status === 'completed' && $record->updated_at ? \Carbon\Carbon::parse($record->updated_at)->timezone('Asia/Manila') : null);
    $gateInFormatted = $gateInCarbon ? $gateInCarbon->format('M d, Y · g:i A') : null;

    // Elapsed Duration
    $tripDuration = null;
    if ($gateOutCarbon && $gateInCarbon && $gateInCarbon->greaterThan($gateOutCarbon)) {
        $diffMinutes = $gateOutCarbon->diffInMinutes($gateInCarbon);
        $hrs = floor($diffMinutes / 60);
        $mins = $diffMinutes % 60;
        if ($hrs > 0 && $mins > 0) {
            $tripDuration = "{$hrs}h {$mins}m";
        } elseif ($hrs > 0) {
            $tripDuration = "{$hrs} hr" . ($hrs > 1 ? 's' : '');
        } else {
            $tripDuration = "{$mins} min" . ($mins > 1 ? 's' : '');
        }
    }

    // Vehicle details
    $vehName = \App\Models\Vehicle::getVehicleName($record->vehicle);
    $vehPlate = \App\Models\Vehicle::getPlateNumber($record->vehicle);

    // Total Passenger Count across all requests
    $grandTotalPax = 0;
    foreach($allRequests as $r) {
        $rawCount = (int) ($r->number_of_passengers ?: 0);
        if ($rawCount <= 0) {
            $pList = is_array($r->passenger_names) ? $r->passenger_names : (json_decode($r->passenger_names ?? '[]', true) ?: []);
            $rawCount = max(count($pList), 1);
        }
        $grandTotalPax += $rawCount;
    }

    $isUrgent = $allRequests->contains(fn ($r) => (bool) $r->is_urgent);

    $badgeColor = match ($record->status) {
        'pending' => 'warning',
        'active' => 'info',
        'completed' => 'success',
        'cancelled' => 'danger',
        default => 'gray',
    };

    $statusLabel = match ($record->status) {
        'pending' => 'Pending Dispatch',
        'active' => 'On Trip / In Transit',
        'completed' => 'Completed',
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
        color: #1f2937;
        width: 100%;
        box-sizing: border-box;
    }
    .dark .trip-modal-wrap {
        color: #f3f4f6;
    }
    .trip-modal-wrap svg {
        width: 15px !important;
        height: 15px !important;
        min-width: 15px !important;
        max-width: 15px !important;
        display: inline-block !important;
        vertical-align: middle !important;
        flex-shrink: 0 !important;
    }
    .trip-box {
        background-color: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 14px 16px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        box-sizing: border-box;
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
        box-sizing: border-box;
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
    .trip-grid-4 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }
    @media (min-width: 640px) {
        .trip-grid-4 {
            grid-template-columns: repeat(4, 1fr);
        }
    }
    .trip-inner-card {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 12px;
        box-sizing: border-box;
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
        font-size: 10.5px;
        color: #6b7280;
        display: block;
        margin-bottom: 2px;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        font-weight: 600;
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
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <span class="trip-label" style="margin-bottom: 0;">Control Sequence</span>
                @if($isCarpool)
                    <span style="display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 9999px; font-size: 10.5px; font-weight: 700; background-color: #f3e8ff; color: #7e22ce; border: 1px solid #d8b4fe;">
                        ⚡ Carpool ({{ $allRequests->count() }} Offices Combined)
                    </span>
                @endif
                @if($isUrgent)
                    <span style="display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 9999px; font-size: 10px; font-weight: 700; background-color: #fee2e2; color: #b91c1c; border: 1px solid #fecaca;">
                        🚨 Urgent
                    </span>
                @endif
            </div>

            <div class="trip-val" style="font-size: 17px; margin-top: 3px; font-family: ui-monospace, SFMono-Regular, monospace; font-weight: 800;">
                {{ $record->formatted_ticket_number }}
            </div>

            <div class="trip-val-sub" style="margin-top: 2px;">
                Official Dispatch Logbook Record &bull; Logged {{ $record->created_at ? $record->created_at->format('M d, Y · g:i A') : 'N/A' }}
            </div>
        </div>

        <div>
            <x-filament::badge :color="$badgeColor" size="lg">
                {{ $statusLabel }}
            </x-filament::badge>
        </div>
    </div>

    <!-- Cancellation Reason (If Cancelled) -->
    @if($record->status === 'cancelled' && $record->cancellation_reason)
        <div class="trip-reason trip-reason-danger">
            <span style="font-size: 16px; line-height: 1;">⚠️</span>
            <div>
                <strong style="display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em;">Trip Cancellation Notice:</strong>
                <span style="font-size: 13px;">{{ $record->cancellation_reason }}</span>
            </div>
        </div>
    @endif

    <!-- Travel Timeline: Departure vs Arrival Grid -->
    <div class="trip-grid-2">
        <!-- Departure Card (Alis) -->
        <div class="trip-box" style="border-left: 4px solid #3b82f6;">
            <div class="trip-title" style="color: #2563eb;">
                <x-filament::icon icon="heroicon-o-clock" style="width: 15px; height: 15px; min-width: 15px;" />
                Departure Details (Pag-alis)
            </div>

            <div class="trip-field">
                <span class="trip-label">Scheduled Departure</span>
                <span class="trip-val" style="font-family: ui-monospace, monospace;">
                    {{ $depDate }} &bull; {{ $depTime }}
                </span>
            </div>

            <div class="trip-field">
                <span class="trip-label">Actual Gate Out Timestamp</span>
                @if($gateOutFormatted)
                    <span class="trip-val" style="color: #2563eb; font-family: ui-monospace, monospace;">
                        {{ $gateOutFormatted }}
                    </span>
                @else
                    <span class="trip-val-sub" style="font-style: italic;">Not logged at gate yet</span>
                @endif
            </div>

            <div class="trip-field">
                <span class="trip-label">Gate Verification Officer</span>
                <span class="trip-val-sub">{{ $record->display_scanned_by ?? 'Gate Security Officer' }}</span>
            </div>
        </div>

        <!-- Arrival Card (Dating / Oras Nakarating) -->
        <div class="trip-box" style="border-left: 4px solid #10b981;">
            <div class="trip-title" style="color: #059669; display: flex; align-items: center; justify-content: space-between;">
                <span style="display: flex; align-items: center; gap: 6px;">
                    <x-filament::icon icon="heroicon-o-check-circle" style="width: 15px; height: 15px; min-width: 15px;" />
                    Arrival Details (Oras Nakarating)
                </span>
                @if($tripDuration)
                    <span style="padding: 1px 7px; border-radius: 9999px; font-size: 10px; font-weight: 700; background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;">
                        ⏱️ {{ $tripDuration }}
                    </span>
                @endif
            </div>

            <div class="trip-field">
                <span class="trip-label">Actual Gate In Timestamp</span>
                @if($gateInFormatted)
                    <span class="trip-val" style="color: #059669; font-family: ui-monospace, monospace;">
                        {{ $gateInFormatted }}
                    </span>
                @elseif($record->status === 'active')
                    <span class="trip-val" style="color: #2563eb;">
                        🚗 On Trip / In Transit
                    </span>
                @else
                    <span class="trip-val-sub" style="font-style: italic;">---</span>
                @endif
            </div>

            <div class="trip-field">
                <span class="trip-label">Trip Duration / Status</span>
                <span class="trip-val">
                    {{ $tripDuration ? "Completed in {$tripDuration}" : ($record->status === 'active' ? 'Vehicle currently in transit' : 'Awaiting departure') }}
                </span>
            </div>

            <div class="trip-field">
                <span class="trip-label">Arrival Verified By</span>
                <span class="trip-val-sub">
                    {{ $gateInFormatted ? ($record->display_scanned_by ?? 'Gate Security Officer') : 'Pending Return' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Assigned Fleet & Driver Dispatch (2-Column Grid) -->
    <div class="trip-grid-2">
        <!-- 1. Assigned Vehicle -->
        <div class="trip-box">
            <div class="trip-title" style="color: #4b5563;">
                <x-filament::icon icon="heroicon-o-truck" style="width: 15px; height: 15px; min-width: 15px;" />
                Assigned Vehicle
            </div>

            <div class="trip-field">
                <span class="trip-label">Vehicle Name & Model</span>
                <span class="trip-val" style="font-size: 14px; font-weight: 800;">{{ $vehName }}</span>
            </div>

            @if($vehPlate && $vehPlate !== $vehName)
                <div class="trip-field">
                    <span class="trip-label">Plate / Conduction Sticker</span>
                    <span style="display: inline-block; padding: 2px 8px; font-size: 11px; font-weight: 700; font-family: ui-monospace, monospace; border-radius: 4px; background-color: #e2e8f0; color: #1e293b; border: 1px solid #cbd5e1;">
                        {{ $vehPlate }}
                    </span>
                </div>
            @endif
        </div>

        <!-- 2. Designated Driver -->
        <div class="trip-box">
            <div class="trip-title" style="color: #4b5563;">
                <x-filament::icon icon="heroicon-o-user" style="width: 15px; height: 15px; min-width: 15px;" />
                Designated Driver
            </div>

            <div class="trip-field">
                <span class="trip-label">Driver Name</span>
                <span class="trip-val" style="font-size: 14px; font-weight: 800;">{{ $record->driver?->name ?? 'Unassigned Driver' }}</span>
            </div>

            @if($record->driver?->contact_number)
                <div class="trip-field">
                    <span class="trip-label">Contact / Phone Number</span>
                    <a href="tel:{{ $record->driver->contact_number }}" class="trip-val" style="color: #2563eb; font-size: 13px; text-decoration: underline; display: inline-flex; align-items: center; gap: 4px;">
                        📞 {{ $record->driver->contact_number }}
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Office & Passengers Manifest -->
    <div class="trip-box">
        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e5e7eb; padding-bottom: 8px; margin-bottom: 12px;">
            <div class="trip-title" style="margin-bottom: 0; color: #1f2937;">
                <x-filament::icon icon="heroicon-o-users" style="width: 15px; height: 15px; min-width: 15px;" />
                Office & Passengers Manifest
            </div>
            <span style="font-size: 11.5px; font-weight: 700; color: #374151;">
                Total: <strong style="color: #2563eb; font-family: ui-monospace, monospace;">{{ $grandTotalPax }} {{ $grandTotalPax == 1 ? 'person' : 'persons' }}</strong>
            </span>
        </div>

        <div style="display: flex; flex-direction: column; gap: 10px;">
            @foreach($allRequests as $index => $req)
                @php
                    $rawPax = $req->passenger_names ?? [];
                    if (is_string($rawPax)) {
                        $rawPax = json_decode($rawPax, true) ?? [];
                    }
                    $pList = [];
                    if (is_array($rawPax)) {
                        foreach ($rawPax as $p) {
                            if (is_array($p) && !empty($p['name'])) {
                                $pList[] = trim($p['name']);
                            } elseif (is_string($p) && !empty(trim($p))) {
                                $pList[] = trim($p);
                            }
                        }
                    }
                    if (empty($pList) && $req->employee_name) {
                        $pList[] = $req->employee_name;
                    }

                    $reqPaxCount = (int) ($req->number_of_passengers ?: max(count($pList), 1));
                @endphp

                <div class="trip-inner-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; flex-wrap: wrap; gap: 6px;">
                        <div>
                            <span style="font-weight: 700; font-size: 13px; color: #1e40af;">
                                🏛️ {{ $req->department ?? 'General Department' }}
                            </span>
                            <span class="trip-val-sub" style="margin-left: 6px;">
                                &bull; Requested by: <strong>{{ $req->employee_name ?? 'N/A' }}</strong>
                            </span>
                        </div>

                        <span style="padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; font-family: ui-monospace, monospace; background-color: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;">
                            {{ $reqPaxCount }} {{ $reqPaxCount == 1 ? 'person' : 'persons' }}
                        </span>
                    </div>

                    <div class="trip-grid-2" style="border-top: 1px dashed #e2e8f0; padding-top: 8px; margin-bottom: 8px;">
                        <div>
                            <span class="trip-label">Destination</span>
                            <span class="trip-val" style="font-size: 12px;">📍 {{ $req->destination ?? 'Official Destination' }}</span>
                        </div>
                        <div>
                            <span class="trip-label">Purpose of Travel</span>
                            <span class="trip-val-sub" style="font-size: 12px;">📝 {{ $req->purpose ?? 'Official University Travel' }}</span>
                        </div>
                    </div>

                    @if(!empty($pList))
                        <div style="border-top: 1px dashed #e2e8f0; padding-top: 6px;">
                            <span class="trip-label" style="margin-bottom: 4px;">Passenger List ({{ count($pList) }})</span>
                            <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                @foreach($pList as $pName)
                                    <span class="trip-pill">
                                        👤 {{ $pName }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if(!empty($req->other_passengers))
                        <div class="trip-val-sub" style="font-style: italic; margin-top: 6px;">
                            Other Passenger Notes: {{ $req->other_passengers }}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    @if(!empty($record->document))
        <!-- Action Links / Buttons Footer -->
        <div class="trip-footer-actions">
            <x-filament::button
                tag="a"
                href="{{ route('trip-tickets.view-signed-document', $record->id) }}"
                target="_blank"
                icon="heroicon-o-document-check"
                color="gray"
                size="sm"
                outlined
            >
                View Signed Document
            </x-filament::button>
        </div>
    @endif
</div>
