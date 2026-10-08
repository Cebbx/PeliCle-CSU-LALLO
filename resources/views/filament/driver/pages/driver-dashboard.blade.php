<x-filament-panels::page>
    @php
        $activeTrip = $this->getActiveTrip();
    @endphp

    <style>
        .driver-dashboard-container {
            font-family: 'Outfit', 'Inter', 'Segoe UI', sans-serif;
        }
        
        /* Banner Card */
        .hero-banner {
            background: linear-gradient(135deg, #1e3a8a 0%, #0f172a 100%);
            color: #ffffff;
            padding: 24px;
            border-radius: 16px;
            margin-bottom: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }
        .hero-banner h2 {
            font-size: 22px;
            font-weight: 800;
            margin: 0 0 6px 0;
            color: #ffffff;
            line-height: 1.2;
        }
        .hero-banner p {
            font-size: 13px;
            color: #cbd5e1;
            margin: 0;
            font-family: monospace;
        }
        .hero-status-card {
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 12px;
            padding: 12px 18px;
            min-width: 220px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.25);
        }
        .status-badge-title {
            font-size: 10px;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            display: block;
        }
        .status-badge-val {
            font-size: 20px;
            font-weight: 800;
            color: #ffffff;
            margin-top: 2px;
            display: block;
            line-height: 1.2;
        }
        .status-val-available {
            color: #4ade80 !important;
            text-shadow: 0 0 12px rgba(74, 222, 128, 0.4);
        }
        .status-val-unavailable {
            color: #f87171 !important;
            text-shadow: 0 0 12px rgba(248, 113, 113, 0.4);
        }
        .status-val-ontrip {
            color: #fbbf24 !important;
            text-shadow: 0 0 12px rgba(251, 191, 36, 0.4);
        }
        .status-emoji-icon {
            font-size: 24px;
        }
        .btn-toggle-status-banner {
            background: rgba(30, 41, 59, 0.95);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.18);
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 6px;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-toggle-available {
            background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
            color: #ffffff !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
            box-shadow: 0 2px 8px rgba(5, 150, 105, 0.4) !important;
        }
        .btn-toggle-available:hover {
            background: linear-gradient(135deg, #047857 0%, #065f46 100%) !important;
            transform: translateY(-1px);
        }
        .btn-toggle-unavailable {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%) !important;
            color: #ffffff !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
            box-shadow: 0 2px 8px rgba(220, 38, 38, 0.4) !important;
        }
        .btn-toggle-unavailable:hover {
            background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%) !important;
            transform: translateY(-1px);
        }

        /* Alert Active Trip Card */
        .banner-alert {
            background: #fffbeb;
            border-left: 4px solid #f59e0b;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            color: #b45309;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        .dark .banner-alert {
            background: #78350f;
            border-left-color: #f59e0b;
            color: #fef3c7;
        }
        .banner-alert p {
            margin: 0 0 12px 0;
            font-size: 14px;
            font-weight: 700;
        }
        
        /* Action Buttons Row */
        .alert-actions-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            margin-top: 12px;
        }
        .btn-alert-action {
            background: #d97706;
            color: white;
            padding: 8px 14px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 11px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-alert-action:hover {
            background: #b45309;
        }
        .btn-milestone {
            background: #10b981;
            color: white;
            padding: 8px 14px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 11px;
            border: none;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-milestone:hover {
            background: #059669;
        }
        .btn-milestone:disabled {
            background: #cbd5e1;
            color: #94a3b8;
            cursor: not-allowed;
        }
        .dark .btn-milestone:disabled {
            background: #334155;
            color: #475569;
        }
        .btn-emergency {
            background: #ef4444;
            color: white;
            padding: 8px 14px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 11px;
            border: none;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-emergency:hover {
            background: #dc2626;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .stat-card-link {
            text-decoration: none !important;
            cursor: pointer !important;
            transition: all 0.2s ease-in-out !important;
        }
        .stat-card-link:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
            border-color: #cbd5e1 !important;
        }
        .dark .stat-card {
            background: #182232;
            border-color: #2d3748;
        }
        .dark .stat-card-link:hover {
            border-color: #475569 !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4) !important;
        }

        /* Driver Modal Styles */
        .driver-modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(6px);
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }
        .driver-modal-card {
            background: #ffffff;
            border-radius: 16px;
            width: 100%;
            max-width: 520px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
            overflow: hidden;
            border: 1px solid #e2e8f0;
            animation: driverModalFadeIn 0.2s ease-out;
        }
        .dark .driver-modal-card {
            background: #0f172a !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }
        .dark .driver-modal-card label {
            color: #cbd5e1 !important;
        }
        .dark .driver-modal-card input,
        .dark .driver-modal-card select,
        .dark .driver-modal-card textarea {
            background: #1e293b !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }
        .dark .driver-modal-card .driver-modal-footer {
            background: #1e293b !important;
            border-color: #334155 !important;
        }
        @keyframes driverModalFadeIn {
            from { opacity: 0; transform: scale(0.96) translateY(8px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
        .stat-label {
            font-size: 10px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .stat-val {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            margin-top: 4px;
            display: block;
        }
        .dark .stat-val {
            color: #ffffff;
        }
        .stat-emoji {
            font-size: 24px;
        }
        
        .btn-toggle-duty {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 9px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 8px;
            transition: all 0.2s;
        }
        .dark .btn-toggle-duty {
            background: #1e293b;
            color: #cbd5e1;
            border-color: #334155;
        }
        .btn-toggle-duty:hover {
            background: #e2e8f0;
            color: #0f172a;
        }
        .dark .btn-toggle-duty:hover {
            background: #334155;
            color: white;
        }

        /* Layout Columns */
        .dashboard-layout {
            display: grid;
            grid-template-columns: 2.2fr 1fr;
            gap: 24px;
        }
        @media (max-width: 1024px) {
            .dashboard-layout {
                grid-template-columns: 1fr;
            }
        }

        /* Card panels */
        .card-panel {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            margin-bottom: 24px;
        }
        .dark .card-panel {
            background: #182232;
            border-color: #2d3748;
        }
        .card-header {
            background: #f8fafc;
            padding: 16px 20px;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 800;
            color: #0f172a;
            font-size: 14px;
        }
        .dark .card-header {
            background: #1e293b;
            border-bottom-color: #2d3748;
            color: #ffffff;
        }
        .card-body {
            padding: 20px;
        }

        /* Structured Trip Table */
        .table-container {
            width: 100%;
            overflow-x: auto;
        }
        .trip-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 13px;
        }
        .trip-table th {
            background: #f1f5f9;
            color: #475569;
            font-weight: 700;
            padding: 12px 16px;
            text-transform: uppercase;
            font-size: 10px;
            border-bottom: 2px solid #e2e8f0;
        }
        .dark .trip-table th {
            background: #1e293b;
            color: #94a3b8;
            border-bottom-color: #2d3748;
        }
        .trip-table td {
            padding: 14px 16px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }
        .dark .trip-table td {
            color: #cbd5e1;
            border-bottom-color: #2d3748;
        }
        .trip-table tr:hover {
            background: #f8fafc;
        }
        .dark .trip-table tr:hover {
            background: #1f2937;
        }

        /* Status Badge */
        .badge-status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 9999px;
            font-weight: 800;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }
        .badge-active {
            background: #ffedd5;
            color: #c2410c;
        }
        .dark .badge-active {
            background: #7c2d12;
            color: #ffedd5;
        }
        .badge-pending {
            background: #eff6ff;
            color: #1d4ed8;
        }
        .dark .badge-pending {
            background: #172554;
            color: #dbeafe;
        }
        .badge-completed {
            background: #dcfce7;
            color: #15803d;
        }
        .dark .badge-completed {
            background: #14532d;
            color: #bbf7d0;
        }
        .badge-cancelled {
            background: #fee2e2;
            color: #b91c1c;
        }
        .dark .badge-cancelled {
            background: #7f1d1d;
            color: #fecaca;
        }

        /* Action Buttons */
        .btn-view {
            background: #3b82f6;
            color: #ffffff;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 11px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s;
        }
        .btn-view:hover {
            background: #2563eb;
        }

        /* Guidelines & Contacts */
        .rules-list {
            list-style: decimal;
            padding-left: 20px;
            margin: 0;
            font-size: 12px;
            line-height: 1.6;
            color: #475569;
        }
        .dark .rules-list {
            color: #94a3b8;
        }
        .rules-list li {
            margin-bottom: 8px;
        }
        .contact-item {
            border-bottom: 1px solid #f1f5f9;
            padding: 10px 0;
            display: flex;
            justify-content: space-between;
            font-size: 12px;
        }
        .dark .contact-item {
            border-bottom-color: #2d3748;
        }
        .contact-item:last-child {
            border-bottom: none;
        }
        .contact-name {
            color: #64748b;
        }
        .dark .contact-name {
            color: #94a3b8;
        }
        .contact-num {
            font-weight: 700;
            color: #0f172a;
        }
        .dark .contact-num {
            color: #ffffff;
        }
    </style>

    <div class="driver-dashboard-container">
        
        <!-- Welcome Hero Banner -->
        <div class="hero-banner">
            <div>
                <h2>Hello, {{ auth()->user()->name }}!</h2>
                <p>License ID: {{ auth()->user()->email }} &nbsp;|&nbsp; Duty: Official Driver</p>
            </div>
            
            @php
                $driver = \App\Models\Driver::where('name', auth()->user()->name)->first();
                $driverStatus = $driver?->status ?? 'available';
                $isOffline = in_array($driverStatus, ['off_duty', 'unavailable']);
            @endphp
            <!-- Duty Status Card inside Banner matching screenshot -->
            <div class="hero-status-card">
                <div>
                    <span class="status-badge-title">DUTY STATUS</span>
                    <span class="status-badge-val {{ $driverStatus === 'on_trip' ? 'status-val-ontrip' : ($isOffline ? 'status-val-unavailable' : 'status-val-available') }}">
                        @if($driverStatus === 'on_trip')
                            On Trip
                        @elseif($isOffline)
                            Offline (Off-Duty)
                        @else
                            Available
                        @endif
                    </span>
                    @if($driverStatus !== 'on_trip')
                        <button wire:click="toggleDutyStatus" class="btn-toggle-status-banner {{ $isOffline ? 'btn-toggle-available' : 'btn-toggle-unavailable' }}">
                            @if($isOffline)
                                🟢 <span>Go Online (Available)</span>
                            @else
                                🔴 <span>Go Offline (Off-Duty)</span>
                            @endif
                        </button>
                    @endif
                </div>
                <div class="status-emoji-icon">
                    @if($driverStatus === 'on_trip')
                        🚗
                    @elseif($isOffline)
                        🔴
                    @else
                        🟢
                    @endif
                </div>
            </div>
        </div>

        <!-- Alert Active Trip & Interactive Logs -->
        @if($activeTrip)
             @php
                $depLogged = $this->hasLoggedDeparture($activeTrip->id);
                $arrLogged = $this->hasLoggedArrival($activeTrip->id);
                $completionUrl = route('trip-tickets.complete-via-qr', ['ticket_number' => $activeTrip->ticket_number]);
                $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' . urlencode($completionUrl);
            @endphp
            <div class="banner-alert">
                <p>🚨 ON-GOING TRIP: Destination: <strong>{{ $activeTrip->vehicleRequest?->destination ?? 'N/A' }}</strong></p>
                <div style="font-size: 12px; margin-bottom: 12px; border-top: 1px solid rgba(0,0,0,0.05); padding-top: 10px;">
                    <strong>Travel Logs:</strong>
                    @if(!$depLogged)
                        <span style="opacity: 0.8;">Departure not logged.</span>
                    @elseif($depLogged && !$arrLogged)
                        <span style="color: #10b981; font-weight: bold;">🛫 Departure Logged!</span> &middot; <span style="opacity: 0.8;">Not arrived yet.</span>
                    @else
                        <span style="color: #10b981; font-weight: bold;">🛫 Departure & 🛬 Arrival Logged!</span> &middot; <span style="font-weight: bold;">Present QR at gate desk.</span>
                    @endif
                </div>

                <!-- Embedded Gate Clearance QR Code -->
                <div class="flex flex-col items-center justify-center p-5 bg-amber-500/5 dark:bg-amber-400/5 border border-amber-500/20 rounded-2xl my-4 max-w-[280px] mx-auto gap-3 text-center shadow-inner">
                    <img src="{{ $qrCodeUrl }}" alt="Trip QR Code" class="w-40 h-40 rounded-xl shadow-md border-2 border-white dark:border-slate-800 bg-white p-1" />
                    <div>
                        <p class="text-[11px] text-amber-800 dark:text-amber-200 font-extrabold uppercase tracking-wider">Gate Clearance QR Code</p>
                        <p class="text-[10px] text-amber-700/85 dark:text-amber-300/80 mt-1">Ipakita ang QR code na ito sa Security Guard sa gate para i-scan at makumpleto ang biyahe.</p>
                    </div>
                </div>
                
                <div class="alert-actions-row">
                    <a href="{{ \App\Filament\Driver\Resources\TripTickets\TripTicketResource::getUrl('view', ['record' => $activeTrip->id]) }}" class="btn-alert-action">
                        📄 View Trip Details
                    </a>
                    
                    @if(!$depLogged)
                        <button wire:click="logDeparture" class="btn-milestone">
                            🛫 Log Departure
                        </button>
                    @elseif($depLogged && !$arrLogged)
                        <button wire:click="logArrival" class="btn-milestone">
                            🛬 Log Arrival
                        </button>
                    @endif

                    <button type="button" wire:click="openBreakdownModal" class="btn-emergency">
                        ⚠️ Report Breakdown
                    </button>
                </div>
            </div>
        @endif

        <!-- Quick Statistics Panel (Clickable Widgets) -->
        <div class="stats-grid">
            <a href="{{ \App\Filament\Driver\Resources\TripTickets\TripTicketResource::getUrl('index') }}" class="stat-card stat-card-link" title="Tingnan ang lahat ng naka-assign na trips">
                <div>
                    <span class="stat-label">Assigned Trips</span>
                    <span class="stat-val">{{ count($this->getAssignedTrips()) }}</span>
                </div>
                <div class="stat-emoji">📋</div>
            </a>

            <a href="{{ \App\Filament\Driver\Resources\TripTickets\TripTicketResource::getUrl('index') }}" class="stat-card stat-card-link" title="Tingnan ang mga natapos na biyahe">
                <div>
                    <span class="stat-label">Completed Trips</span>
                    <span class="stat-val">{{ $this->getCompletedTripsCount() }}</span>
                </div>
                <div class="stat-emoji">🏁</div>
            </a>

            @if($activeTrip)
                <a href="{{ \App\Filament\Driver\Resources\TripTickets\TripTicketResource::getUrl('view', ['record' => $activeTrip->id]) }}" class="stat-card stat-card-link" title="Tingnan ang detalye ng kasalukuyang sasakyan / biyahe">
                    <div>
                        <span class="stat-label">Active Vehicle</span>
                        <span class="stat-val">{{ $activeTrip->formatted_vehicle }}</span>
                    </div>
                    <div class="stat-emoji">🚗</div>
                </a>
            @else
                <div class="stat-card">
                    <div>
                        <span class="stat-label">Active Vehicle</span>
                        <span class="stat-val">None</span>
                    </div>
                    <div class="stat-emoji">🚗</div>
                </div>
            @endif
        </div>

        <!-- Two Column Workspace -->
        <div class="dashboard-layout">
            
            <!-- Left: Assigned Trips Table -->
            <div class="card-panel">
                <div class="card-header flex justify-between items-center">
                    <span>Recent Trip Activities & Schedules</span>
                    <a href="{{ \App\Filament\Driver\Resources\TripTickets\TripTicketResource::getUrl('index') }}" class="text-xs font-semibold text-primary-600 dark:text-primary-400 hover:underline">
                        View All Trips &rarr;
                    </a>
                </div>
                <div class="table-container">
                    <table class="trip-table">
                        <thead>
                            <tr>
                                <th>Ticket No.</th>
                                <th>Destination</th>
                                <th>Schedule (Date & Time)</th>
                                <th>Vehicle</th>
                                <th>Status</th>
                                <th style="text-align: center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($this->getAssignedTrips() as $trip)
                                <tr>
                                    <td style="font-weight: 700; font-family: monospace;">
                                        {{ $trip->ticket_number }}
                                    </td>
                                    <td style="font-weight: 600;">
                                        {{ $trip->vehicleRequest?->destination ?? 'N/A' }}
                                    </td>
                                    <td>
                                        <strong>{{ $trip->vehicleRequest?->date ? \Carbon\Carbon::parse($trip->vehicleRequest->date)->format('M d, Y') : 'N/A' }}</strong>
                                        <div style="font-size: 11px; color: #94a3b8; margin-top: 2px;">
                                            {{ $trip->vehicleRequest?->time ? \Carbon\Carbon::parse($trip->vehicleRequest->time)->format('g:i A') : 'N/A' }}
                                        </div>
                                    </td>
                                    <td>
                                        {{ $trip->formatted_vehicle }}
                                    </td>
                                    <td>
                                        <span class="badge-status {{ $trip->status === 'active' ? 'badge-active' : ($trip->status === 'completed' ? 'badge-completed' : ($trip->status === 'cancelled' ? 'badge-cancelled' : 'badge-pending')) }}">
                                            {{ $trip->status === 'active' ? 'On Trip' : ucfirst($trip->status) }}
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        <a href="{{ \App\Filament\Driver\Resources\TripTickets\TripTicketResource::getUrl('view', ['record' => $trip->id]) }}" class="btn-view">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 32px; color: #94a3b8; font-style: italic;">
                                        No recent trips or activities found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right: Rules & Contacts -->
            <div>
                
                <!-- Travel Guidelines -->
                <div class="card-panel">
                    <div class="card-header">
                        Travel Guidelines
                    </div>
                    <div class="card-body">
                        <ul class="rules-list">
                            <li>Verify that the **Vehicle Log** has been signed before heading out.</li>
                            <li>Maintain passenger limit capacity rules.</li>
                            <li>Request fuel using printed Gasoline Slips.</li>
                            <li>Scan your QR Code with the Gate Guard to complete trips.</li>
                        </ul>
                    </div>
                </div>

                <!-- Emergency Contacts -->
                <div class="card-panel">
                    <div class="card-header">
                        GSO Hotline
                    </div>
                    <div class="card-body" style="padding: 10px 20px;">
                        <div class="contact-item">
                            <span class="contact-name">GSO Main Office</span>
                            <span class="contact-num">0917-555-8888</span>
                        </div>
                        <div class="contact-item">
                            <span class="contact-name">Joel Tumamao (GSO)</span>
                            <span class="contact-num">0953-113-537</span>
                        </div>
                        <div class="contact-item">
                            <span class="contact-name">Security Gate</span>
                            <span class="contact-num">Local 104</span>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <!-- Emergency Breakdown Report Modal -->
    <!-- Emergency Breakdown Report Modal -->
    @if($showBreakdownModal && $activeTrip)
        <div class="driver-modal-backdrop" wire:keydown.escape="closeBreakdownModal">
            <div class="driver-modal-card" style="max-height: 92vh; display: flex; flex-direction: column;">
                <!-- Modal Header -->
                <div style="background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%); color: #ffffff; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-size: 15px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                            <span>🚨</span>
                            <span>Vehicle Emergency & Breakdown Report</span>
                        </div>
                        <div style="font-size: 11px; color: #fecaca; margin-top: 2px;">
                            Trip Ticket: <strong>{{ $activeTrip->ticket_number }}</strong> &bull; Vehicle: <strong>{{ $activeTrip->formatted_vehicle ?? $activeTrip->vehicle }}</strong>
                        </div>
                    </div>
                    <button type="button" wire:click="closeBreakdownModal" style="background: rgba(255,255,255,0.18); border: none; color: #ffffff; width: 28px; height: 28px; border-radius: 50%; font-weight: bold; cursor: pointer; font-size: 16px; display: flex; align-items: center; justify-content: center;" title="Close">
                        &times;
                    </button>
                </div>

                <!-- Modal Body -->
                <div style="padding: 18px 20px; overflow-y: auto; flex: 1; display: flex; flex-direction: column; gap: 14px;">
                    <!-- Category Selection (Quick-tap cards with Bilingual labels) -->
                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #1e293b; display: block; margin-bottom: 6px;">
                            <span>Select Incident Type / Uri ng Aberya:</span> <span style="color: #ef4444;">*</span>
                        </label>
                        @php
                            $categories = [
                                'Flat Tire' => [
                                    'title' => '🛞 Flat Tire',
                                    'sub' => 'Pumutok / Nasiraan ng Gulong',
                                ],
                                'Engine Failure' => [
                                    'title' => '⚙️ Engine Failure',
                                    'sub' => 'Tirik / Sira ang Makina',
                                ],
                                'Overheating' => [
                                    'title' => '🌡️ Engine Overheating',
                                    'sub' => 'Mataas ang Temperatura',
                                ],
                                'Battery Issue' => [
                                    'title' => '🔋 Battery / Electrical',
                                    'sub' => 'Ayaw mag-start / Kuryente',
                                ],
                                'Brake & Steering' => [
                                    'title' => '🛑 Brake / Steering Issue',
                                    'sub' => 'Sira ang Preno o Manibela',
                                ],
                                'Road Accident' => [
                                    'title' => '💥 Vehicular Accident',
                                    'sub' => 'Banggaan sa Kalsada',
                                ],
                                'Other Emergency' => [
                                    'title' => '❓ Other Emergency',
                                    'sub' => 'Iba pang Aberya sa Daan',
                                ],
                            ];
                        @endphp
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 7px;">
                            @foreach($categories as $key => $cat)
                                @php
                                    $isSelected = $breakdownCategory === $key;
                                    $colSpan = $key === 'Other Emergency' ? 'grid-column: span 2;' : '';
                                @endphp
                                <button type="button" 
                                    wire:click="$set('breakdownCategory', '{{ $key }}')"
                                    style="{{ $colSpan }} padding: 9px 12px; text-align: left; border-radius: 8px; cursor: pointer; transition: all 0.15s; border: 1.5px solid {{ $isSelected ? '#dc2626' : '#cbd5e1' }}; background: {{ $isSelected ? '#fef2f2' : '#ffffff' }}; box-shadow: {{ $isSelected ? '0 0 0 2px rgba(220, 38, 38, 0.15)' : 'none' }}; display: flex; flex-direction: column; justify-content: center;">
                                    <div style="font-size: 12px; font-weight: 800; color: {{ $isSelected ? '#991b1b' : '#0f172a' }}; display: flex; align-items: center; justify-content: space-between;">
                                        <span>{{ $cat['title'] }}</span>
                                        @if($isSelected)
                                            <span style="font-size: 11px; color: #dc2626;">✓</span>
                                        @endif
                                    </div>
                                    <div style="font-size: 10.5px; font-weight: 500; color: {{ $isSelected ? '#b91c1c' : '#64748b' }}; margin-top: 1px;">
                                        {{ $cat['sub'] }}
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Location Input -->
                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #1e293b; display: block; margin-bottom: 4px;">
                            📍 Incident Location / Landmark <span style="font-size: 11px; font-weight: 500; color: #64748b;">(Saan nangyari?)</span>
                        </label>
                        <input type="text" 
                            wire:model="breakdownLocation" 
                            placeholder="e.g. Maharlika Highway, Lal-lo (tapat ng gas station o barangay hall)..."
                            style="width: 100%; font-size: 12px; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; background: #ffffff; color: #0f172a;" />
                    </div>

                    <!-- Passenger Status -->
                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #1e293b; display: block; margin-bottom: 4px;">
                            👥 Passenger Safety Status <span style="font-size: 11px; font-weight: 500; color: #64748b;">(Kalagayan ng mga Pasahero)</span>
                        </label>
                        <select wire:model="passengerStatus" style="width: 100%; font-size: 12px; font-weight: 600; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; background: #ffffff; color: #0f172a;">
                            <option value="Safe roadside with Driver">🟢 Safe roadside with Driver (Ligtas lahat sa tabi ng daan)</option>
                            <option value="Transferred to other transport">🟡 Passengers transferred / commuted (Nakasakay na sa ibang biyahe)</option>
                            <option value="Requires Emergency Rescue">🔴 Requires emergency medical / rescue assistance (Nangangailangan ng saklolo)</option>
                        </select>
                    </div>

                    <!-- Comments / Explanation -->
                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #1e293b; display: block; margin-bottom: 4px;">
                            💬 Driver's Remarks & Details <span style="color: #ef4444;">*</span> <span style="font-size: 11px; font-weight: 500; color: #64748b;">(Paliwanag kung anong nangyari)</span>
                        </label>
                        <textarea 
                            wire:model="breakdownComment" 
                            rows="3" 
                            placeholder="e.g. Pumutok po ang gulong sa likod habang bumibiyahe. Nakatabi po kami sa ligtas na bahagi ng daan. May reserba pero kailangan po ng vulcanizing o rescue..."
                            style="width: 100%; font-size: 12px; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; resize: vertical; background: #ffffff; color: #0f172a; font-family: inherit; line-height: 1.45;"></textarea>
                    </div>

                    <!-- Notice Warning -->
                    <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 10px 12px; font-size: 11px; color: #92400e; display: flex; gap: 8px; align-items: flex-start;">
                        <span style="font-size: 14px;">⚠️</span>
                        <div>
                            <strong>Notice:</strong> Submitting this report will notify GSO Admin in real-time, cancel the current trip, and set vehicle status to <strong>Maintenance</strong>.
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="driver-modal-footer" style="padding: 12px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" wire:click="closeBreakdownModal" style="background: #ffffff; border: 1px solid #cbd5e1; color: #475569; padding: 8px 16px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer;">
                        Cancel (Kanselahin)
                    </button>
                    <button type="button" 
                        wire:click="submitBreakdownReport" 
                        wire:loading.attr="disabled"
                        style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); color: #ffffff; border: none; padding: 8px 18px; border-radius: 8px; font-size: 12px; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(220, 38, 38, 0.4);">
                        <span wire:loading.remove wire:target="submitBreakdownReport">🚨 Submit Emergency Report</span>
                        <span wire:loading wire:target="submitBreakdownReport">Submitting Alert...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
