@php
    $ticket = $tripTicket ?? $record->tripTicket;
    $status = $record->status;
    $isUrgent = (bool) $record->is_urgent;

    // Dates & Times
    $depDate = $record->date ? \Carbon\Carbon::parse($record->date)->format('F d, Y') : '---';
    $depTime = $record->time ? \Carbon\Carbon::parse($record->time)->format('g:i A') : '---';
    $retDate = $record->return_date ? \Carbon\Carbon::parse($record->return_date)->format('F d, Y') : null;
    $retTime = $record->return_time ? \Carbon\Carbon::parse($record->return_time)->format('g:i A') : null;

    // Gate Out (Departure)
    $gateOutCarbon = $ticket?->gate_out_at ? \Carbon\Carbon::parse($ticket->gate_out_at)->timezone('Asia/Manila') : null;
    $gateOutFormatted = $gateOutCarbon ? $gateOutCarbon->format('M d, Y · g:i A') : null;

    // Gate In (Arrival)
    $gateInCarbon = $ticket?->gate_in_at ? \Carbon\Carbon::parse($ticket->gate_in_at)->timezone('Asia/Manila') : null;
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
    } elseif ($gateOutCarbon && $status === 'on_trip') {
        $diffMinutes = $gateOutCarbon->diffInMinutes(now('Asia/Manila'));
        $hrs = floor($diffMinutes / 60);
        $mins = $diffMinutes % 60;
        if ($hrs > 0) {
            $tripDuration = "{$hrs}h {$mins}m in transit";
        } else {
            $tripDuration = "{$mins}m in transit";
        }
    }

    // Vehicle & Driver
    $vehRaw = $record->vehicle ?: ($ticket?->vehicle ?? null);
    $vehName = \App\Models\Vehicle::getVehicleName($vehRaw) ?: 'To be assigned';
    $vehPlate = \App\Models\Vehicle::getPlateNumber($vehRaw);
    $driverName = $ticket?->driver?->name ?: 'To be assigned';
    $driverContact = $ticket?->driver?->contact_number;

    // Guard / Scanner
    $guardName = $ticket?->display_scanned_by ?? ($ticket?->scanned_by ?: 'CSU Gate Security');

    // Status Badge & Label
    $badgeColor = match ($status) {
        'pending' => 'warning',
        'approved' => !empty($record->document) ? 'info' : 'success',
        'on_trip' => 'info',
        'completed' => 'success',
        'rejected' => 'danger',
        'cancelled' => 'danger',
        'expired' => 'gray',
        default => 'gray',
    };

    $statusLabel = match ($status) {
        'pending' => 'Pending Admin Review',
        'approved' => !empty($record->document) ? 'Approved & Ready for Departure' : 'Approved (Awaiting CEO Document)',
        'on_trip' => '🚗 On Trip / In Transit',
        'completed' => 'Trip Completed',
        'rejected' => 'Disapproved',
        'cancelled' => 'Cancelled',
        'expired' => 'Expired / Lapsed',
        default => ucfirst($status),
    };

    // Passenger count
    $paxCount = (int) ($record->number_of_passengers ?: 0);
    if ($paxCount <= 0) {
        $pList = is_array($record->passenger_names) ? $record->passenger_names : (json_decode($record->passenger_names ?? '[]', true) ?: []);
        $paxCount = max(count($pList), 1);
    }

    // Stepper Flags
    $isCancelled = in_array($status, ['cancelled', 'expired']);
    $isRejected = ($status === 'rejected');

    // Step 1: Request Filed
    $step1Done = true;

    // Step 2: Approved & Ticketed
    $step2Done = in_array($status, ['approved', 'on_trip', 'completed']);
    $step2Active = ($status === 'pending');
    $step2Failed = ($status === 'rejected');

    // Step 3: CEO Signed Document Attached & Verified
    $hasDoc = !empty($record->document) || !empty($ticket?->document);
    $isDocVerified = !empty($record->document_verified_at) || !empty($ticket?->document_verified_at);
    $step3Done = $hasDoc && $isDocVerified;
    $step3Active = ($status === 'approved' && (!$hasDoc || !$isDocVerified));

    // Step 4: Gate Out / Departure (Pagkaalis)
    $hasDeparted = (bool) $gateOutCarbon || $status === 'on_trip' || $status === 'completed';
    $step4Done = (bool) $gateOutCarbon || $status === 'completed';
    $step4Active = ($status === 'on_trip' && !$gateOutCarbon) || ($step3Done && $status === 'approved');

    // Step 5: Gate In / Arrival (Pagkarating)
    $hasArrived = (bool) $gateInCarbon || $status === 'completed';
    $step5Done = $hasArrived;
    $step5Active = ($status === 'on_trip');
@endphp

<style>
    .tt-modal-wrap {
        display: flex;
        flex-direction: column;
        gap: 16px;
        font-family: inherit;
        color: #1e293b;
        width: 100%;
        box-sizing: border-box;
    }
    .dark .tt-modal-wrap {
        color: #f1f5f9;
    }
    .tt-modal-wrap svg {
        display: inline-block !important;
        vertical-align: middle !important;
        flex-shrink: 0 !important;
    }

    /* Top Summary Header */
    .tt-header-box {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 16px 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }
    .dark .tt-header-box {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        border-color: #334155;
    }

    /* Metric Grid */
    .tt-metrics-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 10px;
    }
    @media (min-width: 640px) {
        .tt-metrics-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }
    .tt-metric-card {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px 14px;
        box-sizing: border-box;
    }
    .dark .tt-metric-card {
        background-color: #18181b;
        border-color: #27272a;
    }
    .tt-metric-label {
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 5px;
        margin-bottom: 3px;
    }
    .dark .tt-metric-label {
        color: #94a3b8;
    }
    .tt-metric-val {
        font-size: 13px;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.3;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .dark .tt-metric-val {
        color: #f8fafc;
    }
    .tt-metric-sub {
        font-size: 11px;
        color: #64748b;
        margin-top: 2px;
    }
    .dark .tt-metric-sub {
        color: #94a3b8;
    }

    /* Stepper Container */
    .tt-timeline-container {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }
    .dark .tt-timeline-container {
        background-color: #18181b;
        border-color: #27272a;
    }

    .tt-step {
        display: flex;
        gap: 16px;
        position: relative;
        padding-bottom: 24px;
    }
    .tt-step:last-child {
        padding-bottom: 0;
    }

    /* Vertical line connecting steps */
    .tt-step:not(:last-child)::after {
        content: '';
        position: absolute;
        top: 32px;
        left: 17px;
        bottom: 0;
        width: 2px;
        background-color: #e2e8f0;
    }
    .dark .tt-step:not(:last-child)::after {
        background-color: #27272a;
    }
    .tt-step.is-done:not(:last-child)::after {
        background-color: #10b981;
    }

    /* Node Circle */
    .tt-node {
        width: 36px;
        height: 36px;
        border-radius: 9999px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-weight: 700;
        font-size: 13px;
        z-index: 1;
        transition: all 0.2s ease;
        box-shadow: 0 0 0 3px #ffffff;
    }
    .dark .tt-node {
        box-shadow: 0 0 0 3px #18181b;
    }

    .tt-node-done {
        background-color: #10b981;
        color: #ffffff;
    }
    .tt-node-active {
        background-color: #3b82f6;
        color: #ffffff;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.25) !important;
        animation: ttPulse 2s infinite;
    }
    .tt-node-pending {
        background-color: #f1f5f9;
        color: #94a3b8;
        border: 2px dashed #cbd5e1;
    }
    .dark .tt-node-pending {
        background-color: #27272a;
        color: #64748b;
        border-color: #3f3f46;
    }
    .tt-node-failed {
        background-color: #ef4444;
        color: #ffffff;
    }

    @keyframes ttPulse {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.06);
        }
    }

    /* Step Content Box */
    .tt-content {
        flex: 1;
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 16px;
        box-sizing: border-box;
    }
    .dark .tt-content {
        background-color: #27272a;
        border-color: #3f3f46;
    }
    .tt-content-highlight {
        border-color: #93c5fd;
        background: linear-gradient(180deg, #eff6ff 0%, #f8fafc 100%);
    }
    .dark .tt-content-highlight {
        border-color: #1e40af;
        background: linear-gradient(180deg, #172554 0%, #18181b 100%);
    }

    .tt-step-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 6px;
    }
    .tt-step-title {
        font-size: 13.5px;
        font-weight: 700;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .dark .tt-step-title {
        color: #f8fafc;
    }
    .tt-step-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 8px;
        border-radius: 9999px;
        font-size: 11px;
        font-weight: 600;
    }

    .tt-data-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 8px;
        margin-top: 8px;
        font-size: 12px;
    }
    @media (min-width: 640px) {
        .tt-data-grid {
            grid-template-columns: 1fr 1fr;
        }
    }
    .tt-data-item {
        display: flex;
        flex-direction: column;
    }
    .tt-data-label {
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #64748b;
        margin-bottom: 1px;
    }
    .dark .tt-data-label {
        color: #94a3b8;
    }
    .tt-data-val {
        font-size: 12.5px;
        font-weight: 600;
        color: #0f172a;
    }
    .dark .tt-data-val {
        color: #f1f5f9;
    }

    /* Aberya / Notice Box */
    .tt-alert {
        padding: 12px 14px;
        border-radius: 10px;
        font-size: 12.5px;
        line-height: 1.4;
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }
    .tt-alert-danger {
        background-color: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }
    .dark .tt-alert-danger {
        background-color: rgba(153, 27, 27, 0.2);
        border-color: rgba(153, 27, 27, 0.4);
        color: #fca5a5;
    }

    /* Footer Action Buttons */
    .tt-actions-bar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        padding-top: 4px;
    }
    .tt-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.15s ease;
        border: 1px solid transparent;
        cursor: pointer;
    }
    .tt-btn-primary {
        background-color: #2563eb;
        color: #ffffff;
    }
    .tt-btn-primary:hover {
        background-color: #1d4ed8;
    }
    .tt-btn-secondary {
        background-color: #ffffff;
        color: #334155;
        border-color: #cbd5e1;
    }
    .tt-btn-secondary:hover {
        background-color: #f8fafc;
        border-color: #94a3b8;
    }
    .dark .tt-btn-secondary {
        background-color: #27272a;
        color: #e2e8f0;
        border-color: #3f3f46;
    }
    .dark .tt-btn-secondary:hover {
        background-color: #3f3f46;
    }
</style>

<div class="tt-modal-wrap">
    <!-- Top Summary Header Box -->
    <div class="tt-header-box">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; flex-wrap: wrap;">
            <div>
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 4px;">
                    <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #2563eb;">
                        Vehicle Request #{{ $record->formatted_request_number }}
                    </span>
                    @if($ticket)
                        <span style="padding: 2px 8px; border-radius: 9999px; font-size: 11px; font-weight: 700; font-family: ui-monospace, monospace; background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;">
                            Trip Ticket: {{ $ticket->ticket_number }}
                        </span>
                    @endif
                    @if($isUrgent)
                        <span style="padding: 2px 8px; border-radius: 9999px; font-size: 10.5px; font-weight: 700; background-color: #fee2e2; color: #b91c1c; border: 1px solid #fecaca;">
                            🚨 Urgent Request
                        </span>
                    @endif
                </div>

                <div style="font-size: 16px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 6px;">
                    <span style="font-size: 18px;">📍</span>
                    <span>{{ $record->destination }}</span>
                </div>

                <div style="font-size: 12px; color: #64748b; margin-top: 3px;">
                    Purpose: <strong style="color: #334155;">{{ $record->purpose }}</strong>
                    @if($record->description)
                        <span style="font-style: italic;"> &bull; {{ $record->description }}</span>
                    @endif
                </div>
            </div>

            <div>
                <x-filament::badge :color="$badgeColor" size="lg">
                    {{ $statusLabel }}
                </x-filament::badge>
            </div>
        </div>
    </div>

    <!-- 4 Key Metric Cards -->
    <div class="tt-metrics-grid">
        <div class="tt-metric-card">
            <span class="tt-metric-label">👤 Requester</span>
            <div class="tt-metric-val" title="{{ $record->employee_name }}">{{ $record->employee_name }}</div>
            <div class="tt-metric-sub">{{ $record->department }}</div>
        </div>

        <div class="tt-metric-card">
            <span class="tt-metric-label">🚐 Vehicle</span>
            <div class="tt-metric-val" title="{{ $vehName }}">{{ $vehName }}</div>
            <div class="tt-metric-sub">
                @if($ticket?->distance_traveled)
                    {{ $vehPlate ?: 'CSU Fleet' }} &bull; 🚗 {{ number_format($ticket->distance_traveled) }} km
                @elseif($ticket?->start_odometer)
                    {{ $vehPlate ?: 'CSU Fleet' }} &bull; 📟 {{ number_format($ticket->start_odometer) }} km
                @else
                    {{ $vehPlate ? "Plate: {$vehPlate}" : 'CSU Fleet' }}
                @endif
            </div>
        </div>

        <div class="tt-metric-card">
            <span class="tt-metric-label">🪪 Driver</span>
            <div class="tt-metric-val" title="{{ $driverName }}">{{ $driverName }}</div>
            <div class="tt-metric-sub">{{ $driverContact ?: 'Assigned by GSO' }}</div>
        </div>

        <div class="tt-metric-card">
            <span class="tt-metric-label">👥 Passengers</span>
            <div class="tt-metric-val">{{ $paxCount }} {{ $paxCount === 1 ? 'Person' : 'Persons' }}</div>
            <div class="tt-metric-sub">{{ $tripDuration ? "⏱️ {$tripDuration}" : 'Scheduled Trip' }}</div>
        </div>
    </div>

    <!-- Disapproval / Cancellation Notice (if applicable) -->
    @if($isRejected && $record->rejection_reason)
        <div class="tt-alert tt-alert-danger">
            <span style="font-size: 20px; line-height: 1;">❌</span>
            <div>
                <strong style="display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #b91c1c;">
                    Disapproval Reason:
                </strong>
                <span style="font-size: 13px; font-weight: 600;">{{ $record->rejection_reason }}</span>
            </div>
        </div>
    @elseif($isCancelled && ($record->cancellation_reason || $ticket?->cancellation_reason))
        <div class="tt-alert tt-alert-danger">
            <span style="font-size: 20px; line-height: 1;">⚠️</span>
            <div>
                <strong style="display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #b91c1c;">
                    Cancellation Notice:
                </strong>
                <span style="font-size: 13px; font-weight: 600;">
                    {{ $record->cancellation_reason ?: $ticket?->cancellation_reason }}
                </span>
            </div>
        </div>
    @endif

    <!-- Visual Progress Stepper (Travel Timeline Tracker) -->
    <div class="tt-timeline-container">
        <div style="font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #2563eb; margin-bottom: 16px; display: flex; align-items: center; gap: 6px;">
            <span>🧭</span>
            <span>Official Travel Timeline & Gate Security Tracking</span>
        </div>

        <!-- STAGE 1: Request Filed -->
        <div class="tt-step is-done">
            <div class="tt-node tt-node-done">
                <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                </svg>
            </div>
            <div class="tt-content">
                <div class="tt-step-header">
                    <div class="tt-step-title">
                        <span>📝 Stage 1: Vehicle Requisition Filed</span>
                    </div>
                    <span class="tt-step-badge" style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;">
                        Filed
                    </span>
                </div>
                <div class="tt-data-grid">
                    <div class="tt-data-item">
                        <span class="tt-data-label">Date & Time Filed</span>
                        <span class="tt-data-val">{{ $record->created_at ? $record->created_at->timezone('Asia/Manila')->format('M d, Y · g:i A') : 'N/A' }}</span>
                    </div>
                    <div class="tt-data-item">
                        <span class="tt-data-label">Requested By</span>
                        <span class="tt-data-val">{{ $record->employee_name }} ({{ $record->department }})</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- STAGE 2: Approved & Ticket Dispatched -->
        <div class="tt-step {{ $step2Done ? 'is-done' : '' }}">
            <div class="tt-node {{ $step2Done ? 'tt-node-done' : ($step2Failed ? 'tt-node-failed' : ($step2Active ? 'tt-node-active' : 'tt-node-pending')) }}">
                @if($step2Done)
                    <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                    </svg>
                @elseif($step2Failed)
                    <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                @else
                    <span>2</span>
                @endif
            </div>
            <div class="tt-content">
                <div class="tt-step-header">
                    <div class="tt-step-title">
                        <span>🎫 Stage 2: Approved & Trip Ticket Dispatched</span>
                    </div>
                    @if($step2Done)
                        <span class="tt-step-badge" style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;">
                            Approved & Assigned
                        </span>
                    @elseif($step2Failed)
                        <span class="tt-step-badge" style="background-color: #fee2e2; color: #b91c1c; border: 1px solid #fecaca;">
                            Disapproved
                        </span>
                    @else
                        <span class="tt-step-badge" style="background-color: #fef3c7; color: #b45309; border: 1px solid #fde68a;">
                            Pending Review
                        </span>
                    @endif
                </div>
                <div class="tt-data-grid">
                    <div class="tt-data-item">
                        <span class="tt-data-label">Trip Ticket Number</span>
                        <span class="tt-data-val" style="font-family: ui-monospace, monospace;">
                            {{ $ticket?->ticket_number ?? 'To be created upon approval' }}
                        </span>
                    </div>
                    <div class="tt-data-item">
                        <span class="tt-data-label">Assigned Vehicle & Driver</span>
                        <span class="tt-data-val">{{ $vehName }} &bull; {{ $driverName }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- STAGE 3: CEO Authority to Travel Document -->
        <div class="tt-step {{ $step3Done ? 'is-done' : '' }}">
            <div class="tt-node {{ $step3Done ? 'tt-node-done' : ($step3Active ? 'tt-node-active' : 'tt-node-pending') }}">
                @if($step3Done)
                    <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                    </svg>
                @else
                    <span>3</span>
                @endif
            </div>
            <div class="tt-content">
                <div class="tt-step-header">
                    <div class="tt-step-title">
                        <span>📄 Stage 3: CEO Signed Travel Authority</span>
                    </div>
                    @if($step3Done)
                        <span class="tt-step-badge" style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;">
                            ✅ Verified & Cleared by Admin
                        </span>
                    @elseif($hasDoc)
                        <span class="tt-step-badge" style="background-color: #fef3c7; color: #b45309; border: 1px solid #fde68a;">
                            📄 Doc Uploaded (Awaiting Admin Review & SMS)
                        </span>
                    @elseif($status === 'approved')
                        <span class="tt-step-badge" style="background-color: #fef3c7; color: #b45309; border: 1px solid #fde68a;">
                            ⏳ Awaiting Signed Document
                        </span>
                    @else
                        <span class="tt-step-badge" style="background-color: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1;">
                            Pending Step 2
                        </span>
                    @endif
                </div>
                <div class="tt-data-grid">
                    <div class="tt-data-item">
                        <span class="tt-data-label">Document Verification Status</span>
                        <span class="tt-data-val">
                            @if($step3Done)
                                Verified by Admin / GSO & Driver Dispatched
                            @elseif($hasDoc)
                                Attached by Requester &bull; For Admin Review & SMS Dispatch
                            @elseif($status === 'approved')
                                Required before vehicle can depart gate
                            @else
                                Not yet required
                            @endif
                        </span>
                    </div>
                    <div class="tt-data-item">
                        <span class="tt-data-label">Travel Order Authorization</span>
                        <span class="tt-data-val">Office of the Campus Executive Officer (CEO)</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- STAGE 4: Departure (Pagkaalis) -->
        <div class="tt-step {{ $step4Done ? 'is-done' : '' }}">
            <div class="tt-node {{ $step4Done ? 'tt-node-done' : ($step4Active ? 'tt-node-active' : 'tt-node-pending') }}">
                @if($step4Done)
                    <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                    </svg>
                @elseif($step4Active)
                    <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
                    </svg>
                @else
                    <span>4</span>
                @endif
            </div>
            <div class="tt-content {{ $step4Active && !$step5Done ? 'tt-content-highlight' : '' }}">
                <div class="tt-step-header">
                    <div class="tt-step-title" style="color: #2563eb;">
                        <span>🛫 Stage 4: Departure Details (Pagkaalis mula sa CSU)</span>
                    </div>
                    @if($gateOutFormatted)
                        <span class="tt-step-badge" style="background-color: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;">
                            Gate Out Confirmed
                        </span>
                    @elseif($status === 'on_trip')
                        <span class="tt-step-badge" style="background-color: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;">
                            🚗 Currently Departed
                        </span>
                    @elseif($hasDoc && $status === 'approved')
                        <span class="tt-step-badge" style="background-color: #fef3c7; color: #b45309; border: 1px solid #fde68a;">
                            ⏳ Ready for Gate Verification
                        </span>
                    @else
                        <span class="tt-step-badge" style="background-color: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1;">
                            Scheduled
                        </span>
                    @endif
                </div>

                <div class="tt-data-grid">
                    <div class="tt-data-item">
                        <span class="tt-data-label">Scheduled Departure</span>
                        <span class="tt-data-val" style="font-family: ui-monospace, monospace;">
                            {{ $depDate }} &bull; {{ $depTime }}
                        </span>
                    </div>

                    <div class="tt-data-item">
                        <span class="tt-data-label">Actual Gate Out Timestamp (Pagkaalis)</span>
                        @if($gateOutFormatted)
                            <span class="tt-data-val" style="color: #2563eb; font-weight: 700; font-family: ui-monospace, monospace;">
                                {{ $gateOutFormatted }}
                            </span>
                        @else
                            <span class="tt-data-val" style="font-style: italic; color: #64748b;">
                                {{ $status === 'on_trip' ? 'Departed (Awaiting guard timestamp)' : 'Awaiting physical departure at security gate' }}
                            </span>
                        @endif
                    </div>

                    <div class="tt-data-item">
                        <span class="tt-data-label">Gate Verification Officer</span>
                        <span class="tt-data-val">{{ $guardName }}</span>
                    </div>

                    <div class="tt-data-item" style="grid-column: span 2;">
                        <span class="tt-data-label">Initial Odometer Reading (Pagkaalis)</span>
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-top: 4px;">
                            <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 8px; font-family: ui-monospace, monospace; font-size: 13px; font-weight: 700; background-color: #0f172a; color: #38bdf8; border: 1px solid #1e293b; box-shadow: inset 0 1px 3px rgba(0,0,0,0.4);">
                                <span style="font-size: 10px; opacity: 0.8; letter-spacing: 0.05em; text-transform: uppercase; color: #94a3b8;">ODO</span>
                                <span>{{ $ticket?->start_odometer ? number_format($ticket->start_odometer) . ' km' : 'Logged upon departure' }}</span>
                            </span>
                            @if($ticket?->start_odometer)
                                <span style="font-size: 11.5px; color: #64748b; font-weight: 500;">
                                    Verified at security gate before departure
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- STAGE 5: Arrival (Pagkarating) -->
        <div class="tt-step {{ $step5Done ? 'is-done' : '' }}">
            <div class="tt-node {{ $step5Done ? 'tt-node-done' : ($step5Active ? 'tt-node-active' : 'tt-node-pending') }}">
                @if($step5Done)
                    <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                    </svg>
                @elseif($step5Active)
                    <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
                    </svg>
                @else
                    <span>5</span>
                @endif
            </div>
            <div class="tt-content {{ $step5Done ? 'tt-content-highlight' : '' }}">
                <div class="tt-step-header">
                    <div class="tt-step-title" style="color: #059669;">
                        <span>🛬 Stage 5: Arrival Details (Pagkarating / Pagbalik sa CSU)</span>
                    </div>
                    @if($gateInFormatted || $status === 'completed')
                        <span class="tt-step-badge" style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;">
                            Trip Completed & Arrived
                        </span>
                    @elseif($status === 'on_trip')
                        <span class="tt-step-badge" style="background-color: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;">
                            🚗 In Transit / En Route
                        </span>
                    @else
                        <span class="tt-step-badge" style="background-color: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1;">
                            Pending Return
                        </span>
                    @endif
                </div>

                <div class="tt-data-grid">
                    <div class="tt-data-item">
                        <span class="tt-data-label">Target Return Schedule</span>
                        <span class="tt-data-val" style="font-family: ui-monospace, monospace;">
                            @if($retDate)
                                {{ $retDate }} {{ $retTime ? "&bull; {$retTime}" : '' }}
                            @else
                                Same day return
                            @endif
                        </span>
                    </div>

                    <div class="tt-data-item">
                        <span class="tt-data-label">Actual Gate In Timestamp (Pagkarating)</span>
                        @if($gateInFormatted)
                            <span class="tt-data-val" style="color: #059669; font-weight: 700; font-family: ui-monospace, monospace;">
                                {{ $gateInFormatted }}
                            </span>
                        @elseif($status === 'on_trip')
                            <span class="tt-data-val" style="color: #2563eb; font-weight: 600;">
                                🚗 Currently on the road {{ $tripDuration ? "({$tripDuration})" : '' }}
                            </span>
                        @else
                            <span class="tt-data-val" style="font-style: italic; color: #64748b;">
                                Pending vehicle return & gate security sign-in
                            </span>
                        @endif
                    </div>

                    <div class="tt-data-item" style="grid-column: span 2;">
                        <span class="tt-data-label">Final Odometer & Total Distance (Pagbalik)</span>
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-top: 4px;">
                            <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 8px; font-family: ui-monospace, monospace; font-size: 13px; font-weight: 700; background-color: #0f172a; color: #34d399; border: 1px solid #1e293b; box-shadow: inset 0 1px 3px rgba(0,0,0,0.4);">
                                <span style="font-size: 10px; opacity: 0.8; letter-spacing: 0.05em; text-transform: uppercase; color: #94a3b8;">ODO</span>
                                <span>{{ $ticket?->end_odometer ? number_format($ticket->end_odometer) . ' km' : ($status === 'on_trip' ? 'Recording in progress...' : 'Logged upon gate return') }}</span>
                            </span>
                            @if($ticket?->distance_traveled)
                                <span style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 8px; font-size: 12.5px; font-weight: 700; background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;">
                                    🚗 {{ number_format($ticket->distance_traveled) }} km Traveled
                                </span>
                                @if($ticket->start_odometer)
                                    <span style="font-size: 11.5px; color: #64748b; font-weight: 500;">
                                        ({{ number_format($ticket->start_odometer) }} &rarr; {{ number_format($ticket->end_odometer) }} km)
                                    </span>
                                @endif
                            @endif
                        </div>
                    </div>

                    <div class="tt-data-item">
                        <span class="tt-data-label">Total Elapsed Trip Duration</span>
                        <span class="tt-data-val" style="color: #059669; font-weight: 700;">
                            {{ $tripDuration ?: 'Awaiting completion' }}
                        </span>
                    </div>

                    <div class="tt-data-item">
                        <span class="tt-data-label">Gate Verification Officer (Pagdating)</span>
                        <span class="tt-data-val" style="color: #059669; font-weight: 600;">
                            {{ $ticket?->scanned_by ?: ($status === 'completed' ? 'Edward Cabbat (Security Guard)' : 'Logged upon arrival') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Document & Form Links -->
    <div class="tt-actions-bar">
        @if($hasDoc && !empty($record->id))
            <a href="{{ route('vehicle-requests.view-signed-document', $record->id) }}" target="_blank" class="tt-btn tt-btn-secondary">
                <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                </svg>
                View Signed Authority
            </a>
        @endif

        @if($ticket && !empty($ticket->id))
            <a href="{{ route('trip-tickets.print', $ticket->id) }}" target="_blank" class="tt-btn tt-btn-secondary">
                <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z"/>
                </svg>
                Print Trip Ticket (QR)
            </a>
        @endif

        @if(!empty($record->id))
            <a href="{{ route('vehicle-requests.print', $record->id) }}" target="_blank" class="tt-btn tt-btn-primary">
                <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.076-.61-2.11-1.09-3.082m0 0l-1.32-.88a1.5 1.5 0 01-.48-2.072l1.64-2.46a1.5 1.5 0 012.072-.48l1.32.88m-2.232 5.012a20.08 20.08 0 014.288-1.572M17.28 13.829c.24-1.076.61-2.11 1.09-3.082m0 0l1.32-.88a1.5 1.5 0 00.48-2.072l-1.64-2.46a1.5 1.5 0 00-2.072-.48l-1.32.88m2.232 5.012a20.08 20.08 0 00-4.288-1.572M12 18.75a6.75 6.75 0 100-13.5 6.75 6.75 0 000 13.5z"/>
                </svg>
                View Requisition Form
            </a>
        @endif
    </div>
</div>
