@php
    $actionColor = match ($record->action) {
        'Approved & Ticketed', 'Completed Trip', 'Arrival Logged' => 'success',
        'Started Trip', 'Uploaded Document' => 'info',
        'Departure Logged', 'Updated Request' => 'warning',
        'Breakdown Reported', 'Cancelled Trip', 'Cancelled Request', 'Disapproved Request', 'Deleted Request', 'Deleted Ticket' => 'danger',
        'Created Request' => 'primary',
        default => 'gray',
    };

    $targetName = match ($record->model_type) {
        'App\Models\TripTicket' => 'Trip Ticket',
        'App\Models\VehicleRequest' => 'Vehicle Request',
        'App\Models\Driver' => 'Driver',
        'App\Models\Vehicle' => 'Vehicle',
        'App\Models\WithdrawalSlip' => 'Withdrawal Slip',
        default => $record->model_type ? class_basename($record->model_type) : 'System Entity',
    };

    $subject = null;
    if ($record->model_type && class_exists($record->model_type) && $record->model_id) {
        try {
            $subject = $record->model_type::find($record->model_id);
        } catch (\Throwable $e) {
            $subject = null;
        }
    }
@endphp

<style>
    .audit-card {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 14px 16px;
    }
    .dark .audit-card {
        background-color: #18181b;
        border-color: #27272a;
    }

    .audit-label {
        color: #64748b;
        display: block;
        font-size: 11px;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.05em;
        margin-bottom: 3px;
    }
    .dark .audit-label {
        color: #94a3b8;
    }

    .audit-value {
        color: #0f172a;
        font-size: 14px;
        font-weight: 700;
    }
    .dark .audit-value {
        color: #ffffff;
    }

    .audit-subtext {
        color: #475569;
        font-size: 12px;
        margin-top: 2px;
    }
    .dark .audit-subtext {
        color: #cbd5e1;
    }

    .audit-mono {
        font-family: monospace;
        letter-spacing: 0.04em;
    }

    .audit-narrative-box {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-left: 4px solid #2563eb;
        border-radius: 10px;
        padding: 14px 16px;
        font-size: 13.5px;
        line-height: 1.6;
        color: #1e293b;
    }
    .dark .audit-narrative-box {
        background-color: #09090b;
        border-color: #27272a;
        border-left-color: #3b82f6;
        color: #f1f5f9;
    }
</style>

<div class="space-y-4 text-sm">
    <!-- Top Action & Actor Card -->
    <div class="audit-card">
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; font-size: 13px;">
            <div>
                <span class="audit-label">Action Performed</span>
                <div style="display: flex; align-items: center; gap: 6px; margin-top: 2px;">
                    <strong class="audit-value">{{ $record->action }}</strong>
                </div>
            </div>

            <div>
                <span class="audit-label">Timestamp</span>
                <strong class="audit-value">
                    {{ $record->created_at ? $record->created_at->format('M d, Y • h:i:s A') : 'N/A' }}
                </strong>
                @if($record->created_at)
                    <div class="audit-subtext">({{ $record->created_at->diffForHumans() }})</div>
                @endif
            </div>

            <div>
                <span class="audit-label">Performed By</span>
                <strong class="audit-value" style="display: flex; align-items: center; gap: 6px;">
                    <span>{{ $record->user_name ?? ($record->user?->name ?? 'System Automated Process') }}</span>
                </strong>
                @if($record->user?->email)
                    <div class="audit-subtext">{{ $record->user->email }}</div>
                @elseif($record->user_name === 'System')
                    <div class="audit-subtext" style="color: #6366f1;">Automated Background Job / System</div>
                @endif
            </div>

            <div>
                <span class="audit-label">IP Address</span>
                <strong class="audit-value audit-mono" style="display: inline-flex; align-items: center; gap: 4px; color: #0284c7;">
                    {{ $record->ip_address ?: '127.0.0.1 (Localhost)' }}
                </strong>
                <div class="audit-subtext">Security & Traceability Log</div>
            </div>
        </div>
    </div>

    <!-- Target Entity Information -->
    @if($record->model_type)
        <div class="audit-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <span class="audit-label" style="margin-bottom: 0;">Target Entity / Resource</span>
                <span style="font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 9999px; background: rgba(59, 130, 246, 0.1); color: #2563eb;" class="dark:bg-blue-500/20 dark:text-blue-400">
                    {{ $targetName }} #{{ $record->model_id }}
                </span>
            </div>

            <div style="font-size: 13px;" class="audit-subtext">
                @if($subject instanceof \App\Models\TripTicket)
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;">
                        <div><strong>Trip Ticket No:</strong> {{ $subject->ticket_number }}</div>
                        <div><strong>Driver:</strong> {{ $subject->driver?->name ?? 'Unassigned' }}</div>
                        <div><strong>Vehicle:</strong> {{ $subject->vehicle ?? 'N/A' }}</div>
                        <div><strong>Status:</strong> <span style="text-transform: capitalize;">{{ $subject->status }}</span></div>
                    </div>
                @elseif($subject instanceof \App\Models\VehicleRequest)
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;">
                        <div><strong>Request No:</strong> {{ $subject->request_number }}</div>
                        <div><strong>Requester:</strong> {{ $subject->requester_name }}</div>
                        <div><strong>Destination:</strong> {{ $subject->destination }}</div>
                        <div><strong>Status:</strong> <span style="text-transform: capitalize;">{{ $subject->status }}</span></div>
                    </div>
                @elseif($subject instanceof \App\Models\Driver)
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;">
                        <div><strong>Driver Name:</strong> {{ $subject->name }}</div>
                        <div><strong>Contact:</strong> {{ $subject->contact_number }}</div>
                        <div><strong>License:</strong> {{ $subject->license_number }}</div>
                        <div><strong>Status:</strong> <span style="text-transform: capitalize;">{{ $subject->status }}</span></div>
                    </div>
                @elseif($subject instanceof \App\Models\Vehicle)
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;">
                        <div><strong>Plate Number:</strong> {{ $subject->plate_number }}</div>
                        <div><strong>Model:</strong> {{ $subject->model }}</div>
                        <div><strong>Type:</strong> {{ $subject->type }}</div>
                        <div><strong>Status:</strong> <span style="text-transform: capitalize;">{{ $subject->status }}</span></div>
                    </div>
                @else
                    <div>Entity Type: <code class="audit-mono">{{ $record->model_type }}</code> (ID: {{ $record->model_id }})</div>
                @endif
            </div>
        </div>
    @endif

    <!-- Activity Log Details / Narrative -->
    <div>
        <span class="audit-label" style="margin-bottom: 6px;">Audit Log Narrative</span>
        <div class="audit-narrative-box">
            {{ $record->details ?: 'No detailed narrative logged for this event.' }}
        </div>
    </div>
</div>
