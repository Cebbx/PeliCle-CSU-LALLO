<x-filament-panels::page>
    @php
        $stats = $this->getSummaryStats();
        $currentFilter = $this->tableFilters['status']['value'] ?? null;
    @endphp

    <!-- Excel-like Summary Ribbon / Status Header -->
    <div class="excel-summary-strip" style="margin-bottom: 12px;">
        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; padding: 10px 16px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div class="excel-icon-box" style="flex-shrink: 0;">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" style="width: 20px; height: 20px;" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 14H6v-2h6v2zm0-4H6v-2h6v2zm0-4H6V7h6v2zm6 8h-4v-2h4v2zm0-4h-4v-2h4v2zm0-4h-4V7h4v2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="excel-title-text" style="margin: 0; line-height: 1.2;">
                        Monthly Vehicle Dispatch Logbook
                    </h3>
                    <p class="excel-subtitle-text" style="margin: 3px 0 0 0;">
                        Period: <span class="excel-period-highlight">{{ $stats['period'] }}</span> &bull; Campus Motorpool Fleet Records
                    </p>
                </div>
            </div>

            <!-- Inline Stats Chips (Interactive Clickable Buttons) -->
            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 8px; font-size: 11px;">
                <button type="button" 
                        wire:click="setStatusFilter(null)" 
                        class="excel-stat-chip {{ $currentFilter === null ? 'is-active' : '' }}" 
                        title="Click to view all trip tickets">
                    Total Tickets: <strong class="excel-chip-val">{{ $stats['total'] }}</strong>
                </button>

                <button type="button" 
                        wire:click="setStatusFilter('completed')" 
                        class="excel-stat-chip {{ $currentFilter === 'completed' ? 'is-active' : '' }}" 
                        title="Click to filter Completed trips">
                    Completed: <strong class="excel-chip-val-success">{{ $stats['completed'] }}</strong>
                </button>

                <button type="button" 
                        wire:click="setStatusFilter('active')" 
                        class="excel-stat-chip {{ $currentFilter === 'active' ? 'is-active' : '' }}" 
                        title="Click to filter On Trip records">
                    On Trip: <strong class="excel-chip-val-info">{{ $stats['onTrip'] }}</strong>
                </button>

                <button type="button" 
                        wire:click="setStatusFilter('pending')" 
                        class="excel-stat-chip {{ $currentFilter === 'pending' ? 'is-active' : '' }}" 
                        title="Click to filter Pending trips">
                    Pending: <strong class="excel-chip-val" style="color: #fbbf24; font-weight: 800; margin-left: 4px;">{{ $stats['pending'] ?? 0 }}</strong>
                </button>

                <button type="button" 
                        wire:click="setStatusFilter('cancelled')" 
                        class="excel-stat-chip {{ $currentFilter === 'cancelled' ? 'is-active' : '' }}" 
                        title="Click to filter Cancelled trips">
                    Cancelled: <strong class="excel-chip-val" style="color: #f87171; font-weight: 800; margin-left: 4px;">{{ $stats['cancelled'] ?? 0 }}</strong>
                </button>

                <button type="button" 
                        wire:click="setStatusFilter('passengers')" 
                        class="excel-stat-chip" 
                        title="Click to see total passenger volume info">
                    Passengers: <strong class="excel-chip-val" style="color: #a78bfa; font-weight: 800; margin-left: 4px;">{{ $stats['passengers'] }} {{ ($stats['passengers'] ?? 0) == 1 ? 'person' : 'persons' }}</strong>
                </button>
            </div>
        </div>
    </div>

    <!-- Excel-like Dense Table Container -->
    <div class="excel-logbook-card">
        {{ $this->table }}
    </div>

    <style>
        /* =========================================================
           1. DARK THEME STYLES (html.dark)
           Fully matches AdminPanelProvider (#070a11 & #0b0f19)
           ========================================================= */
        html.dark .excel-summary-strip {
            background-color: #0b0f19 !important;
            border: 1px solid #1e293b !important;
            border-radius: 8px !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.25) !important;
        }
        html.dark .excel-icon-box {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            background-color: rgba(16, 185, 129, 0.15) !important;
            border: 1px solid rgba(16, 185, 129, 0.3) !important;
            border-radius: 6px !important;
        }
        html.dark .excel-icon-box svg {
            color: #34d399 !important;
        }
        html.dark .excel-title-text {
            color: #f8fafc !important;
            font-size: 13px !important;
            font-weight: 800 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.04em !important;
            margin: 0 !important;
            line-height: 1.2 !important;
        }
        html.dark .excel-subtitle-text {
            color: #94a3b8 !important;
            font-size: 11px !important;
            font-weight: 500 !important;
            margin: 0 !important;
            line-height: 1.3 !important;
        }
        html.dark .excel-period-highlight {
            color: #34d399 !important;
            font-weight: 700 !important;
        }
        html.dark .excel-stat-chip {
            display: inline-flex !important;
            align-items: center !important;
            background-color: #111827 !important;
            border: 1px solid #1e293b !important;
            border-radius: 6px !important;
            padding: 4px 10px !important;
            color: #cbd5e1 !important;
            font-size: 11px !important;
            font-weight: 600 !important;
            white-space: nowrap !important;
            cursor: pointer !important;
            transition: all 0.15s ease-in-out !important;
            outline: none !important;
            user-select: none !important;
        }
        html.dark .excel-stat-chip:hover {
            background-color: #1f2937 !important;
            border-color: #38bdf8 !important;
            color: #f8fafc !important;
            transform: translateY(-1px);
        }
        html.dark .excel-stat-chip:active {
            transform: translateY(0);
        }
        html.dark .excel-stat-chip.is-active {
            border-color: #38bdf8 !important;
            background-color: rgba(56, 189, 248, 0.18) !important;
            box-shadow: 0 0 0 1px #38bdf8, 0 2px 4px rgba(0, 0, 0, 0.4) !important;
            color: #ffffff !important;
        }
        html.dark .excel-chip-val {
            color: #f8fafc !important;
            font-weight: 800 !important;
            margin-left: 4px !important;
        }
        html.dark .excel-chip-val-success {
            color: #4ade80 !important;
            font-weight: 800 !important;
            margin-left: 4px !important;
        }
        html.dark .excel-chip-val-info {
            color: #60a5fa !important;
            font-weight: 800 !important;
            margin-left: 4px !important;
        }

        /* 2. MAIN CARD CONTAINER IN DARK MODE */
        html.dark .excel-logbook-card {
            background-color: #0b0f19 !important;
            border: 1px solid #1e293b !important;
            border-radius: 8px !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3) !important;
            padding: 6px !important;
            color: #f8fafc !important;
            width: 100% !important;
            overflow-x: auto !important;
            overflow-y: visible !important;
        }
        html.dark .excel-logbook-card .fi-ta,
        html.dark .excel-logbook-card .fi-ta-ctn,
        html.dark .excel-logbook-card .fi-ta-content {
            background-color: #0b0f19 !important;
            background: #0b0f19 !important;
            border: none !important;
            box-shadow: none !important;
            color: #f8fafc !important;
            overflow-x: auto !important;
        }
        html.dark .excel-logbook-card .fi-ta-header {
            background-color: #0b0f19 !important;
            border-bottom: 1px solid #1e293b !important;
            padding: 10px 12px !important;
        }
        html.dark .excel-logbook-card .fi-ta-header-heading {
            font-size: 13.5px !important;
            font-weight: 800 !important;
            color: #f8fafc !important;
        }
        html.dark .excel-logbook-card .fi-ta-header-description {
            font-size: 11px !important;
            color: #94a3b8 !important;
        }
        html.dark .excel-logbook-card .fi-ta-header input {
            background-color: #111827 !important;
            border: 1px solid #1e293b !important;
            color: #f8fafc !important;
            font-size: 11px !important;
            border-radius: 6px !important;
            height: 32px !important;
            padding: 2px 10px !important;
        }
        html.dark .excel-logbook-card .fi-ta-header input::placeholder {
            color: #64748b !important;
        }

        /* 3. TABLE GRID IN DARK MODE (100% Contrast & Legibility) */
        html.dark .excel-logbook-card table.fi-ta-table {
            min-width: 1210px !important;
            width: 100% !important;
            border-collapse: collapse !important;
            background-color: #0b0f19 !important;
        }
        /* Table Headers (TH) - CLEAR WHITE BOLD TEXT */
        html.dark .excel-logbook-card .fi-ta-header-cell {
            background-color: #111827 !important;
            border: 1px solid #1e293b !important;
            padding: 8px 10px !important;
            vertical-align: middle !important;
        }
        html.dark .excel-logbook-card .fi-ta-header-cell *,
        html.dark .excel-logbook-card .fi-ta-header-cell-label,
        html.dark .excel-logbook-card .fi-ta-header-cell button,
        html.dark .excel-logbook-card .fi-ta-header-cell span {
            color: #f1f5f9 !important;
            font-size: 11px !important;
            font-weight: 800 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.04em !important;
        }
        html.dark .excel-logbook-card .fi-ta-header-cell svg {
            color: #94a3b8 !important;
        }

        /* Prevent header collision / overlapping */
        .excel-logbook-card .fi-ta-header-cell,
        .excel-logbook-card .fi-ta-cell {
            max-width: none !important;
            box-sizing: border-box !important;
        }
        .excel-logbook-card th:first-child,
        .excel-logbook-card td:first-child {
            width: 38px !important;
            min-width: 38px !important;
            max-width: 42px !important;
        }
        .excel-logbook-card th:nth-child(2),
        .excel-logbook-card td:nth-child(2) {
            min-width: 140px !important;
            width: 145px !important;
        }
        .excel-logbook-card th:nth-child(3),
        .excel-logbook-card td:nth-child(3) {
            min-width: 95px !important;
            width: 95px !important;
        }
        .excel-logbook-card th:nth-child(4),
        .excel-logbook-card td:nth-child(4) {
            min-width: 90px !important;
            width: 90px !important;
        }
        /* Office & Passengers - give plenty of room so it never touches Destination */
        .excel-logbook-card th:nth-child(5),
        .excel-logbook-card td:nth-child(5) {
            min-width: 210px !important;
            width: 220px !important;
        }
        /* Destination */
        .excel-logbook-card th:nth-child(6),
        .excel-logbook-card td:nth-child(6) {
            min-width: 140px !important;
            width: 150px !important;
        }
        .excel-logbook-card th:nth-child(7),
        .excel-logbook-card td:nth-child(7) {
            min-width: 130px !important;
            width: 130px !important;
        }
        /* Driver */
        .excel-logbook-card th:nth-child(8),
        .excel-logbook-card td:nth-child(8) {
            min-width: 110px !important;
            width: 110px !important;
        }
        /* Arrival Time */
        .excel-logbook-card th:nth-child(9),
        .excel-logbook-card td:nth-child(9) {
            min-width: 105px !important;
            width: 105px !important;
        }
        /* Status */
        .excel-logbook-card th:nth-child(10),
        .excel-logbook-card td:nth-child(10) {
            min-width: 95px !important;
            width: 95px !important;
        }
        /* Action (View sa gilid) */
        .excel-logbook-card th:nth-child(11),
        .excel-logbook-card td:nth-child(11) {
            min-width: 80px !important;
            width: 80px !important;
            text-align: center !important;
        }

        /* Table Body Rows */
        html.dark .excel-logbook-card .fi-ta-row {
            background-color: #0b0f19 !important;
            height: 34px !important;
            transition: background-color 0.15s ease-in-out !important;
        }
        html.dark .excel-logbook-card .fi-ta-row:nth-child(even) {
            background-color: #0f1523 !important;
        }
        html.dark .excel-logbook-card .fi-ta-row:hover {
            background-color: #1e293b !important;
        }

        /* Table Cells (TD) */
        html.dark .excel-logbook-card .fi-ta-cell {
            border: 1px solid #1e293b !important;
            padding: 6px 10px !important;
            font-size: 11.5px !important;
            vertical-align: middle !important;
        }
        html.dark .excel-logbook-card .fi-ta-cell:not(.fi-ta-actions-cell) .fi-ta-text-item-label,
        html.dark .excel-logbook-card .fi-ta-cell:not(.fi-ta-actions-cell) p {
            color: #f8fafc !important;
            font-size: 11.5px !important;
            font-weight: 500 !important;
        }

        /* Row Index (#) */
        html.dark .excel-logbook-card .fi-ta-cell:first-child,
        html.dark .excel-logbook-card .fi-ta-cell:first-child * {
            text-align: center !important;
            font-weight: 700 !important;
            color: #94a3b8 !important;
            font-size: 11px !important;
            background-color: #111827 !important;
        }
        /* TT Control No */
        html.dark .excel-logbook-card .fi-ta-cell:nth-child(2) span,
        html.dark .excel-logbook-card .fi-ta-cell:nth-child(2) div {
            color: #60a5fa !important;
            font-weight: 700 !important;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
        }
        /* Date & Time */
        html.dark .excel-logbook-card .fi-ta-cell:nth-child(3) span,
        html.dark .excel-logbook-card .fi-ta-cell:nth-child(4) span {
            color: #cbd5e1 !important;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
        }

        /* Empty State in Dark Mode */
        html.dark .excel-logbook-card .fi-ta-empty-state {
            padding: 36px 16px !important;
            background-color: #0b0f19 !important;
        }
        html.dark .excel-logbook-card .fi-ta-empty-state-icon-ctn {
            background-color: #111827 !important;
            border: 1px solid #1e293b !important;
        }
        html.dark .excel-logbook-card .fi-ta-empty-state-heading {
            color: #f8fafc !important;
            font-size: 14px !important;
            font-weight: 700 !important;
        }
        html.dark .excel-logbook-card .fi-ta-empty-state-description {
            color: #94a3b8 !important;
            font-size: 12px !important;
        }

        /* Pagination in Dark Mode */
        html.dark .excel-logbook-card .fi-ta-pagination {
            background-color: #0b0f19 !important;
            border-top: 1px solid #1e293b !important;
            padding: 8px 12px !important;
        }
        html.dark .excel-logbook-card .fi-ta-pagination * {
            color: #cbd5e1 !important;
        }
        html.dark .excel-logbook-card .fi-ta-pagination button {
            border: 1px solid #1e293b !important;
            background-color: #111827 !important;
            color: #f8fafc !important;
        }

        /* =========================================================
           2. LIGHT THEME STYLES (html:not(.dark))
           Clean White & Slate for Light Mode
           ========================================================= */
        html:not(.dark) .excel-summary-strip {
            background-color: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
        }
        html:not(.dark) .excel-icon-box {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            background-color: #ecfdf5 !important;
            border: 1px solid #a7f3d0 !important;
            border-radius: 6px !important;
        }
        html:not(.dark) .excel-title-text {
            color: #0f172a !important;
            font-size: 13px !important;
            font-weight: 800 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.04em !important;
            margin: 0 !important;
            line-height: 1.2 !important;
        }
        html:not(.dark) .excel-subtitle-text {
            color: #475569 !important;
            font-size: 11px !important;
            font-weight: 500 !important;
            margin: 0 !important;
            line-height: 1.3 !important;
        }
        html:not(.dark) .excel-period-highlight {
            color: #047857 !important;
            font-weight: 700 !important;
        }
        html:not(.dark) .excel-stat-chip {
            display: inline-flex !important;
            align-items: center !important;
            background-color: #f8fafc !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 6px !important;
            padding: 4px 10px !important;
            color: #1e293b !important;
            font-size: 11px !important;
            font-weight: 600 !important;
            white-space: nowrap !important;
            cursor: pointer !important;
            transition: all 0.15s ease-in-out !important;
            outline: none !important;
            user-select: none !important;
        }
        html:not(.dark) .excel-stat-chip:hover {
            background-color: #f1f5f9 !important;
            border-color: #0284c7 !important;
            color: #0f172a !important;
            transform: translateY(-1px);
        }
        html:not(.dark) .excel-stat-chip:active {
            transform: translateY(0);
        }
        html:not(.dark) .excel-stat-chip.is-active {
            border-color: #0284c7 !important;
            background-color: #e0f2fe !important;
            box-shadow: 0 0 0 1px #0284c7, 0 1px 2px rgba(0, 0, 0, 0.1) !important;
            color: #0369a1 !important;
        }
        html:not(.dark) .excel-chip-val {
            color: #0f172a !important;
            font-weight: 800 !important;
            margin-left: 4px !important;
        }
        html:not(.dark) .excel-chip-val-success {
            color: #15803d !important;
            font-weight: 800 !important;
            margin-left: 4px !important;
        }
        html:not(.dark) .excel-chip-val-info {
            color: #1d4ed8 !important;
            font-weight: 800 !important;
            margin-left: 4px !important;
        }

        html:not(.dark) .excel-logbook-card {
            background-color: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08) !important;
            padding: 6px !important;
            color: #0f172a !important;
            width: 100% !important;
            overflow-x: auto !important;
            overflow-y: visible !important;
        }
        html:not(.dark) .excel-logbook-card .fi-ta,
        html:not(.dark) .excel-logbook-card .fi-ta-ctn,
        html:not(.dark) .excel-logbook-card .fi-ta-content {
            background-color: #ffffff !important;
            background: #ffffff !important;
            border: none !important;
            box-shadow: none !important;
            color: #0f172a !important;
            overflow-x: auto !important;
        }
        html:not(.dark) .excel-logbook-card table.fi-ta-table {
            min-width: 1210px !important;
            width: 100% !important;
            border-collapse: collapse !important;
        }
        html:not(.dark) .excel-logbook-card .fi-ta-header {
            background-color: #ffffff !important;
            border-bottom: 1px solid #e2e8f0 !important;
            padding: 10px 12px !important;
        }
        html:not(.dark) .excel-logbook-card .fi-ta-header-heading {
            font-size: 13.5px !important;
            font-weight: 800 !important;
            color: #0f172a !important;
        }
        html:not(.dark) .excel-logbook-card .fi-ta-header-description {
            font-size: 11px !important;
            color: #475569 !important;
        }
        html:not(.dark) .excel-logbook-card .fi-ta-header input {
            background-color: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            color: #0f172a !important;
            font-size: 11px !important;
            border-radius: 6px !important;
            height: 32px !important;
            padding: 2px 10px !important;
        }
        html:not(.dark) .excel-logbook-card .fi-ta-header-cell {
            background-color: #f1f5f9 !important;
            border: 1px solid #cbd5e1 !important;
            padding: 8px 10px !important;
            vertical-align: middle !important;
        }
        html:not(.dark) .excel-logbook-card .fi-ta-header-cell *,
        html:not(.dark) .excel-logbook-card .fi-ta-header-cell-label,
        html:not(.dark) .excel-logbook-card .fi-ta-header-cell button,
        html:not(.dark) .excel-logbook-card .fi-ta-header-cell span {
            color: #0f172a !important;
            font-size: 11px !important;
            font-weight: 800 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.04em !important;
        }
        html:not(.dark) .excel-logbook-card .fi-ta-row {
            background-color: #ffffff !important;
            height: 34px !important;
        }
        html:not(.dark) .excel-logbook-card .fi-ta-row:nth-child(even) {
            background-color: #f8fafc !important;
        }
        html:not(.dark) .excel-logbook-card .fi-ta-row:hover {
            background-color: #f1f5f9 !important;
        }
        html:not(.dark) .excel-logbook-card .fi-ta-cell {
            border: 1px solid #cbd5e1 !important;
            padding: 6px 10px !important;
            font-size: 11.5px !important;
            vertical-align: middle !important;
        }
        html:not(.dark) .excel-logbook-card .fi-ta-cell:not(.fi-ta-actions-cell) .fi-ta-text-item-label,
        html:not(.dark) .excel-logbook-card .fi-ta-cell:not(.fi-ta-actions-cell) p {
            color: #0f172a !important;
            font-size: 11.5px !important;
            font-weight: 500 !important;
        }
        html:not(.dark) .excel-logbook-card .fi-ta-cell:first-child,
        html:not(.dark) .excel-logbook-card .fi-ta-cell:first-child * {
            text-align: center !important;
            font-weight: 700 !important;
            color: #475569 !important;
            font-size: 11px !important;
            background-color: #f1f5f9 !important;
        }
        html:not(.dark) .excel-logbook-card .fi-ta-cell:nth-child(2) span,
        html:not(.dark) .excel-logbook-card .fi-ta-cell:nth-child(2) div {
            color: #1e40af !important;
            font-weight: 700 !important;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
        }
        html:not(.dark) .excel-logbook-card .fi-ta-cell:nth-child(3) span,
        html:not(.dark) .excel-logbook-card .fi-ta-cell:nth-child(4) span {
            color: #1e293b !important;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
        }

        /* 3. UNIVERSAL ACTION BUTTONS & BADGES */
        .excel-logbook-card .fi-ta-actions-cell {
            padding: 3px 6px !important;
            white-space: nowrap !important;
            text-align: center !important;
        }
        .excel-logbook-card .fi-ta-actions-cell a,
        .excel-logbook-card .fi-ta-actions-cell button {
            padding: 4px 8px !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            border-radius: 5px !important;
            margin: 0 2px !important;
        }
    </style>
</x-filament-panels::page>
