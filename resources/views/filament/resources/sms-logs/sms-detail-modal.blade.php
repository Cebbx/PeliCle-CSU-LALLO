<style>
    .sms-modal-card {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 14px 16px;
    }
    .dark .sms-modal-card {
        background-color: #18181b;
        border-color: #27272a;
    }

    .sms-modal-label {
        color: #64748b;
        display: block;
        font-size: 11px;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.05em;
        margin-bottom: 3px;
    }
    .dark .sms-modal-label {
        color: #94a3b8;
    }

    .sms-modal-value {
        color: #0f172a;
        font-size: 14px;
        font-weight: 700;
    }
    .dark .sms-modal-value {
        color: #ffffff;
    }

    .sms-modal-phone {
        color: #0f172a;
        font-size: 14px;
        font-weight: 700;
        font-family: monospace;
        letter-spacing: 0.05em;
    }
    .dark .sms-modal-phone {
        color: #38bdf8;
    }

    .sms-modal-datetime {
        color: #334155;
        font-weight: 600;
        font-size: 13px;
    }
    .dark .sms-modal-datetime {
        color: #e2e8f0;
    }

    .sms-modal-gateway {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-weight: 600;
        font-size: 12px;
        color: #16a34a;
    }
    .dark .sms-modal-gateway {
        color: #4ade80;
    }

    .sms-preview-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }

    .sms-preview-title {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
    }
    .dark .sms-preview-title {
        color: #94a3b8;
    }

    .sms-preview-count {
        font-size: 11px;
        color: #64748b;
        font-family: monospace;
    }
    .dark .sms-preview-count {
        color: #94a3b8;
    }

    .sms-chat-canvas {
        background-color: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 18px;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
    }
    .dark .sms-chat-canvas {
        background-color: #09090b;
        border-color: #27272a;
    }

    .sms-bubble {
        background-color: #2563eb;
        color: #ffffff;
        border-radius: 18px 18px 4px 18px;
        padding: 14px 18px;
        max-width: 96%;
        font-size: 13.5px;
        line-height: 1.6;
        white-space: pre-line;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        word-break: break-word;
    }
    .dark .sms-bubble {
        background-color: #1d4ed8;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
    }

    .sms-bubble-meta {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        color: #64748b;
        margin-top: 6px;
        padding-right: 4px;
    }
    .dark .sms-bubble-meta {
        color: #94a3b8;
    }
</style>

<div class="space-y-4 text-sm">
    <!-- Top Metadata Card -->
    <div class="sms-modal-card">
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; font-size: 13px;">
            <div>
                <span class="sms-modal-label">Recipient Driver</span>
                <strong class="sms-modal-value">{{ $record->driver?->name ?? 'Unassigned / Deleted Driver' }}</strong>
            </div>
            <div>
                <span class="sms-modal-label">Phone Number</span>
                <strong class="sms-modal-phone">{{ $record->phone_number }}</strong>
            </div>
            <div>
                <span class="sms-modal-label">Date & Time Sent</span>
                <span class="sms-modal-datetime">{{ $record->created_at ? $record->created_at->format('F d, Y • h:i A') : 'N/A' }}</span>
            </div>
            <div>
                <span class="sms-modal-label">Dispatch Gateway</span>
                <span class="sms-modal-gateway">
                    <svg style="width: 14px; height: 14px;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                    {{ !empty(config('services.semaphore.key')) ? 'Semaphore SMS API (Live)' : 'In-App Driver Simulator (Local/Testing)' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Realistic Chat Bubble Preview -->
    <div>
        <div class="sms-preview-header">
            <span class="sms-preview-title">
                💬 SMS Text Message Preview
            </span>
            <span class="sms-preview-count">
                {{ strlen($record->message) }} chars
            </span>
        </div>
        
        <div class="sms-chat-canvas">
            <!-- SMS Bubble -->
            <div class="sms-bubble">
{{ trim($record->message) }}
            </div>
            <!-- Status & Timestamp under bubble -->
            <div class="sms-bubble-meta">
                <span>{{ $record->created_at ? $record->created_at->format('g:i A') : '' }}</span>
                <span style="color: #3b82f6; font-weight: bold;">✓✓</span>
            </div>
        </div>
    </div>
</div>
