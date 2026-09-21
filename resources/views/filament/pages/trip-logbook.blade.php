<x-filament-panels::page>
    @php
        $stats = $this->getSummaryStats();
    @endphp

    <!-- Excel-like Summary Ribbon / Status Header -->
    <div class="excel-summary-strip">
        <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-2.5">
            <div class="flex items-center gap-3">
                <div class="excel-icon-box">
                    <svg class="w-5 h-5 text-emerald-700" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 14H6v-2h6v2zm0-4H6v-2h6v2zm0-4H6V7h6v2zm6 8h-4v-2h4v2zm0-4h-4v-2h4v2zm0-4h-4V7h4v2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="excel-title-text">
                        Monthly Vehicle Dispatch Logbook
                    </h3>
                    <p class="excel-subtitle-text">
                        Period: <span class="excel-period-highlight">{{ $stats['period'] }}</span> &bull; Campus Motorpool Fleet Records
                    </p>
                </div>
            </div>

            <!-- Inline Stats Chips (Excel Status Bar Style) -->
            <div class="flex items-center gap-2 text-[11px]">
                <span class="excel-stat-chip">
                    Total Trips: <strong class="excel-chip-val">{{ $stats['total'] }}</strong>
                </span>
                <span class="excel-stat-chip">
                    Completed: <strong class="excel-chip-val-success">{{ $stats['completed'] }}</strong>
                </span>
                <span class="excel-stat-chip">
                    On Trip: <strong class="excel-chip-val-info">{{ $stats['onTrip'] }}</strong>
                </span>
                <span class="excel-stat-chip">
                    Passengers: <strong class="excel-chip-val">{{ $stats['passengers'] }} pax</strong>
                </span>
            </div>
        </div>
    </div>

    <!-- Excel-like Dense Table Container -->
    <div class="excel-logbook-card">
        {{ $this->table }}
    </div>

    <style>
        /* =========================================================
           PLAIN WHITE EXCEL SPREADSHEET STYLING FOR TRIP LOGBOOK
           Guaranteed 100% Dark Text Contrast (No Invisible Text)
           ========================================================= */

        /* 1. TOP SUMMARY STRIP */
        .excel-summary-strip {
            background-color: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 6px !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
        }
        .excel-icon-box {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            background-color: #ecfdf5 !important;
            border: 1px solid #a7f3d0 !important;
            border-radius: 6px !important;
        }
        .excel-title-text {
            color: #0f172a !important;
            font-size: 13px !important;
            font-weight: 800 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.04em !important;
            margin: 0 !important;
            line-height: 1.2 !important;
        }
        .excel-subtitle-text {
            color: #334155 !important;
            font-size: 11px !important;
            font-weight: 500 !important;
            margin: 0 !important;
            line-height: 1.3 !important;
        }
        .excel-period-highlight {
            color: #047857 !important;
            font-weight: 700 !important;
        }
        .excel-stat-chip {
            display: inline-flex !important;
            align-items: center !important;
            background-color: #f1f5f9 !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 4px !important;
            padding: 3px 8px !important;
            color: #1e293b !important;
            font-size: 11px !important;
            font-weight: 600 !important;
            white-space: nowrap !important;
        }
        .excel-chip-val {
            color: #0f172a !important;
            font-weight: 800 !important;
            margin-left: 4px !important;
        }
        .excel-chip-val-success {
            color: #15803d !important;
            font-weight: 800 !important;
            margin-left: 4px !important;
        }
        .excel-chip-val-info {
            color: #1d4ed8 !important;
            font-weight: 800 !important;
            margin-left: 4px !important;
        }

        /* 2. MAIN CARD CONTAINER */
        .excel-logbook-card {
            background-color: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 6px !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08) !important;
            padding: 4px !important;
            color: #0f172a !important;
            width: 100% !important;
            overflow: hidden !important;
        }

        /* Override any Filament dark mode wrapper backgrounds */
        .excel-logbook-card .fi-ta,
        .excel-logbook-card .fi-ta-ctn,
        .excel-logbook-card .fi-ta-content {
            background-color: #ffffff !important;
            background: #ffffff !important;
            border: none !important;
            box-shadow: none !important;
            color: #0f172a !important;
        }

        /* 3. TABLE HEADER, SEARCH BAR, FILTER INDICATORS */
        .excel-logbook-card .fi-ta-header {
            background-color: #ffffff !important;
            border-bottom: 1px solid #e2e8f0 !important;
            padding: 8px 10px !important;
        }
        .excel-logbook-card .fi-ta-header-heading {
            font-size: 13px !important;
            font-weight: 800 !important;
            color: #0f172a !important;
        }
        .excel-logbook-card .fi-ta-header-description {
            font-size: 11px !important;
            color: #475569 !important;
        }
        .excel-logbook-card .fi-ta-header input {
            background-color: #ffffff !important;
            border: 1px solid #94a3b8 !important;
            color: #0f172a !important;
            font-size: 11px !important;
            font-weight: 500 !important;
            height: 32px !important;
            padding: 2px 8px !important;
        }
        .excel-logbook-card .fi-ta-header input::placeholder {
            color: #64748b !important;
        }
        .excel-logbook-card .fi-ta-filter-indicators,
        .excel-logbook-card .fi-ta-filter-indicators * {
            color: #1e293b !important;
            font-size: 11px !important;
            font-weight: 600 !important;
        }
        .excel-logbook-card .fi-badge-color-warning,
        .excel-logbook-card .fi-ta-filter-indicators .fi-badge {
            background-color: #fef3c7 !important;
            color: #92400e !important;
            border: 1px solid #fde68a !important;
        }

        /* 4. EXCEL TABLE GRID */
        .excel-logbook-card table.fi-ta-table {
            width: 100% !important;
            border-collapse: collapse !important;
            background-color: #ffffff !important;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        }

        /* Table Column Headers (TH) */
        .excel-logbook-card .fi-ta-header-cell {
            background-color: #e2e8f0 !important;
            border: 1px solid #cbd5e1 !important;
            padding: 5px 6px !important;
            white-space: nowrap !important;
            vertical-align: middle !important;
        }
        .excel-logbook-card .fi-ta-header-cell-label,
        .excel-logbook-card .fi-ta-header-cell button,
        .excel-logbook-card .fi-ta-header-cell span {
            color: #0f172a !important;
            font-size: 10.5px !important;
            font-weight: 800 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.04em !important;
        }
        .excel-logbook-card .fi-ta-header-cell svg {
            color: #475569 !important;
        }

        /* Table Body Rows (TR) */
        .excel-logbook-card .fi-ta-row {
            background-color: #ffffff !important;
            height: 30px !important;
            transition: background-color 0.1s ease-in-out !important;
        }
        .excel-logbook-card .fi-ta-row:nth-child(even) {
            background-color: #f8fafc !important;
        }
        .excel-logbook-card .fi-ta-row:hover {
            background-color: #e0f2fe !important; /* Soft Excel blue row hover */
        }

        /* Table Cells (TD) - STRICT HIGH CONTRAST DARK TEXT */
        .excel-logbook-card .fi-ta-cell {
            border: 1px solid #cbd5e1 !important;
            padding: 4px 6px !important;
            font-size: 11px !important;
            line-height: 1.25 !important;
            vertical-align: middle !important;
        }

        /* ALL TEXT IN ALL CELLS MUST BE DARK */
        .excel-logbook-card .fi-ta-cell,
        .excel-logbook-card .fi-ta-cell div,
        .excel-logbook-card .fi-ta-cell span,
        .excel-logbook-card .fi-ta-cell p,
        .excel-logbook-card .fi-ta-text-item-label,
        .excel-logbook-card .fi-ta-text-item-description {
            color: #0f172a !important;
            font-size: 11px !important;
            font-weight: 500 !important;
            line-height: 1.25 !important;
        }

        /* Row Index (#) Column */
        .excel-logbook-card .fi-ta-cell:first-child,
        .excel-logbook-card .fi-ta-cell:first-child * {
            text-align: center !important;
            font-weight: 700 !important;
            color: #475569 !important;
            font-size: 10.5px !important;
            width: 32px !important;
            background-color: #f1f5f9 !important;
        }

        /* Ticket Number (TT Control No.) */
        .excel-logbook-card .fi-ta-cell:nth-child(2) span,
        .excel-logbook-card .fi-ta-cell:nth-child(2) div {
            color: #1e40af !important; /* Deep bold royal blue */
            font-weight: 700 !important;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
            font-size: 11px !important;
        }

        /* Date & Time */
        .excel-logbook-card .fi-ta-cell:nth-child(3) span,
        .excel-logbook-card .fi-ta-cell:nth-child(4) span {
            color: #1e293b !important;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
            font-weight: 600 !important;
            font-size: 11px !important;
        }

        /* Status Badges */
        .excel-logbook-card .fi-badge {
            font-size: 10px !important;
            font-weight: 700 !important;
            padding: 2px 8px !important;
            border-radius: 4px !important;
            line-height: 1.2 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.02em !important;
        }
        /* Completed */
        .excel-logbook-card .fi-color-success .fi-badge,
        .excel-logbook-card .fi-badge[class*="success"],
        .excel-logbook-card .fi-badge-color-success {
            background-color: #dcfce7 !important;
            color: #15803d !important;
            border: 1px solid #86efac !important;
        }
        /* On Trip / Active */
        .excel-logbook-card .fi-color-info .fi-badge,
        .excel-logbook-card .fi-badge[class*="info"],
        .excel-logbook-card .fi-badge-color-info {
            background-color: #dbeafe !important;
            color: #1d4ed8 !important;
            border: 1px solid #93c5fd !important;
        }
        /* Pending */
        .excel-logbook-card .fi-color-warning .fi-badge,
        .excel-logbook-card .fi-badge[class*="warning"],
        .excel-logbook-card .fi-badge-color-warning {
            background-color: #fef3c7 !important;
            color: #b45309 !important;
            border: 1px solid #fcd34d !important;
        }
        /* Cancelled / Rejected */
        .excel-logbook-card .fi-color-danger .fi-badge,
        .excel-logbook-card .fi-badge[class*="danger"],
        .excel-logbook-card .fi-badge-color-danger {
            background-color: #fee2e2 !important;
            color: #b91c1c !important;
            border: 1px solid #fca5a5 !important;
        }

        /* Action Buttons (Ticket & Order) */
        .excel-logbook-card .fi-ta-actions-cell {
            padding: 2px 4px !important;
            white-space: nowrap !important;
            text-align: center !important;
        }
        .excel-logbook-card .fi-ta-actions-cell a,
        .excel-logbook-card .fi-ta-actions-cell button {
            padding: 2px 8px !important;
            font-size: 10.5px !important;
            font-weight: 700 !important;
            border-radius: 4px !important;
            margin: 0 1px !important;
        }
        .excel-logbook-card .fi-ta-actions-cell a[href*="print"] {
            background-color: #2563eb !important;
            color: #ffffff !important;
            border: 1px solid #1d4ed8 !important;
        }
        .excel-logbook-card .fi-ta-actions-cell a[href*="print"]:hover {
            background-color: #1d4ed8 !important;
        }
        .excel-logbook-card .fi-ta-actions-cell a[href*="print-travel-order"] {
            background-color: #475569 !important;
            color: #ffffff !important;
            border: 1px solid #334155 !important;
        }
        .excel-logbook-card .fi-ta-actions-cell a[href*="print-travel-order"]:hover {
            background-color: #334155 !important;
        }
        .excel-logbook-card .fi-ta-actions-cell svg {
            width: 12px !important;
            height: 12px !important;
            color: #ffffff !important;
        }

        /* Pagination Strip */
        .excel-logbook-card .fi-ta-pagination {
            background-color: #ffffff !important;
            border-top: 1px solid #cbd5e1 !important;
            padding: 6px 10px !important;
            font-size: 11px !important;
            color: #1e293b !important;
        }
        .excel-logbook-card .fi-ta-pagination * {
            color: #1e293b !important;
            font-size: 11px !important;
            font-weight: 600 !important;
        }
        .excel-logbook-card .fi-ta-pagination button {
            border: 1px solid #cbd5e1 !important;
            background-color: #f8fafc !important;
            color: #0f172a !important;
        }
    </style>
</x-filament-panels::page>
