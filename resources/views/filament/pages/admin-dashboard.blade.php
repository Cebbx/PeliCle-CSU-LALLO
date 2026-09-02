<x-filament-panels::page>
    <style>
        /* =========================================================
           1366x768 (HD Widescreen) & Standard Laptop Optimization
           Fits 100% inside Viewport with ZERO Vertical Scrolling
           ========================================================= */
        
        /* Global Reset for Page Container */
        .fi-page {
            background-color: #070a11 !important;
        }
        .fi-main-ctn {
            max-width: 100% !important;
            padding: 8px 16px 4px 16px !important;
            background-color: #070a11 !important;
        }
        .fi-header {
            display: none !important;
        }

        .dashboard-wrapper {
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: #f8fafc;
            width: 100%;
            background-color: #070a11;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* 1. Header (Compact) */
        .dash-header {
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .dash-title {
            font-size: 20px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.02em;
            margin: 0;
            line-height: 1;
        }
        .dash-welcome {
            font-size: 12.5px;
            font-weight: 700;
            color: #ffffff;
            margin-top: 3px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .dash-desc {
            font-size: 11px;
            color: #94a3b8;
            margin-top: 1px;
            line-height: 1.2;
        }
        .dash-live-status {
            font-size: 10.5px;
            color: #64748b;
            margin-top: 1px;
            font-weight: 500;
        }

        /* 2. Grid Layouts */
        .row-grid-4 {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-bottom: 8px;
        }
        .row-grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: 8px;
        }
        .row-grid-bottom {
            display: grid;
            grid-template-columns: 1fr 1fr 1.22fr;
            gap: 8px;
            margin-bottom: 8px;
        }

        /* 3. Stat Cards */
        .dash-card {
            background: #0d121d;
            border: 1px solid #1a2233;
            border-radius: 10px;
            padding: 10px 12px 0 12px;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 80px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.4);
        }
        .dash-card-pad {
            padding: 10px 12px;
            min-height: 175px;
        }

        .card-top-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-badge-wrap {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .card-icon-round {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            flex-shrink: 0;
        }
        .bg-icon-green { background: #10b981; color: #ffffff; }
        .bg-icon-blue { background: #0284c7; color: #ffffff; }
        .bg-icon-sky { background: #0ea5e9; color: #ffffff; }
        .bg-icon-amber { background: #f59e0b; color: #ffffff; }
        .bg-icon-red { background: #ef4444; color: #ffffff; }

        .card-title-text {
            font-size: 11px;
            font-weight: 600;
            color: #d1d5db;
        }
        .card-corner-icon {
            font-size: 13px;
            opacity: 0.5;
        }

        .card-mid-section {
            margin-top: 4px;
            margin-bottom: 1px;
        }
        .card-number-bold {
            font-size: 18px;
            font-weight: 800;
            color: #ffffff;
            line-height: 1;
            letter-spacing: -0.02em;
            display: flex;
            align-items: baseline;
            gap: 4px;
        }
        .card-unit-label {
            font-size: 13px;
            font-weight: 700;
            color: #ffffff;
        }
        .card-subtitle-note {
            font-size: 9.5px;
            font-weight: 500;
            margin-top: 2px;
        }
        .color-green { color: #10b981; }
        .color-blue { color: #38bdf8; }
        .color-amber { color: #fbbf24; }
        .color-red { color: #f87171; }
        .color-slate { color: #94a3b8; }

        /* Smooth Bottom Sparklines */
        .card-wave-box {
            margin: 2px -12px 0 -12px;
            height: 16px;
            overflow: hidden;
            display: block;
            position: relative;
        }
        .card-wave-svg {
            width: 100%;
            height: 100%;
            display: block;
        }

        /* 4. Bottom Analytics Panels */
        .panel-top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
        }
        .panel-title-text {
            font-size: 11.5px;
            font-weight: 700;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .panel-filter-btn {
            background: #141c2c;
            border: 1px solid #222e44;
            color: #cbd5e1;
            padding: 2px 7px;
            border-radius: 4px;
            font-size: 9.5px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }

        /* Recent Requests Feed */
        .feed-list-box {
            display: flex;
            flex-direction: column;
            gap: 1px;
        }
        .feed-item-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 3px 0;
            border-bottom: 1px solid #161f30;
            gap: 6px;
        }
        .feed-item-row:last-child {
            border-bottom: none;
        }
        .feed-item-left {
            display: flex;
            align-items: center;
            gap: 6px;
            min-width: 0;
        }
        .feed-dot-indicator {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .feed-text-group {
            display: flex;
            align-items: center;
            gap: 6px;
            min-width: 0;
        }
        .feed-ref-code {
            font-size: 10.5px;
            font-weight: 700;
            color: #ffffff;
            white-space: nowrap;
        }
        .feed-purpose-desc {
            font-size: 10px;
            color: #94a3b8;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 140px;
        }
        .feed-item-right {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-shrink: 0;
        }
        .feed-status-pill {
            font-size: 9px;
            font-weight: 700;
            padding: 1px 6px;
            border-radius: 4px;
            display: inline-block;
            text-transform: capitalize;
        }
        .status-pill-pending { background: #f59e0b; color: #000000; }
        .status-pill-approved { background: #0284c7; color: #ffffff; }
        .status-pill-completed { background: #059669; color: #ffffff; }
        .status-pill-rejected { background: #dc2626; color: #ffffff; }

        .feed-timestamp {
            font-size: 9.5px;
            color: #64748b;
            white-space: nowrap;
            min-width: 45px;
            text-align: right;
        }

        /* 5. Bottom Copyright & Version */
        .dash-footer-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 4px;
            border-top: 1px solid #141c2c;
            font-size: 9.5px;
            color: #64748b;
        }
    </style>

    <div class="dashboard-wrapper">

        <!-- Top Greeting Header -->
        <div class="dash-header">
            <div>
                <h1 class="dash-title">Dashboard</h1>
                <div class="dash-welcome">
                    Welcome back, {{ auth()->user()->name ?? 'Admin User' }}! 👋
                </div>
                <div class="dash-desc">
                    Here is the current operational status of the CSU Lal-lo Campus Vehicle & Trip Management System.
                </div>
                <div class="dash-live-status">
                    System Live • {{ \Carbon\Carbon::now('Asia/Manila')->format('h:i A') }}
                </div>
            </div>
        </div>

        <!-- ROW 1: Four Main Fleet Status Cards -->
        <div class="row-grid-4">
            
            <!-- 1. Driver Availability -->
            <div class="dash-card">
                <div class="card-top-row">
                    <div class="card-badge-wrap">
                        <div class="card-icon-round bg-icon-green">
                            <svg style="width: 11px; height: 11px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <span class="card-title-text">Driver Availability</span>
                    </div>
                    <span class="card-corner-icon color-green">👥</span>
                </div>
                <div class="card-mid-section">
                    <div class="card-number-bold">
                        {{ $driverStats['available'] }} / {{ $driverStats['total'] }} <span class="card-unit-label">Available</span>
                    </div>
                    <div class="card-subtitle-note color-green">Drivers ready for dispatch</div>
                </div>
                <!-- Smooth Neon Green Wave -->
                <div class="card-wave-box">
                    <svg viewBox="0 0 300 35" class="card-wave-svg" preserveAspectRatio="none">
                        <path d="M0,30 C70,28 120,6 180,9 C240,12 270,26 300,30" fill="none" stroke="#10b981" stroke-width="2" />
                    </svg>
                </div>
            </div>

            <!-- 2. Vehicle Availability -->
            <div class="dash-card">
                <div class="card-top-row">
                    <div class="card-badge-wrap">
                        <div class="card-icon-round bg-icon-green">
                            <svg style="width: 11px; height: 11px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                        </div>
                        <span class="card-title-text">Vehicle Availability</span>
                    </div>
                    <span class="card-corner-icon color-green">🚚</span>
                </div>
                <div class="card-mid-section">
                    <div class="card-number-bold">
                        {{ $vehicleStats['available'] }} / {{ $vehicleStats['total'] }} <span class="card-unit-label">Available</span>
                    </div>
                    <div class="card-subtitle-note color-green">Vehicles ready for dispatch</div>
                </div>
                <!-- Smooth Neon Green Wave -->
                <div class="card-wave-box">
                    <svg viewBox="0 0 300 35" class="card-wave-svg" preserveAspectRatio="none">
                        <path d="M0,28 C60,30 120,4 180,10 C240,16 270,6 300,4" fill="none" stroke="#10b981" stroke-width="2" />
                    </svg>
                </div>
            </div>

            <!-- 3. Pending Vehicle Requests -->
            <div class="dash-card">
                <div class="card-top-row">
                    <div class="card-badge-wrap">
                        <div class="card-icon-round bg-icon-blue">
                            <svg style="width: 11px; height: 11px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <span class="card-title-text">Pending Vehicle Requests</span>
                    </div>
                    <span class="card-corner-icon" style="color: #64748b;">📄</span>
                </div>
                <div class="card-mid-section">
                    <div class="card-number-bold">{{ $pendingRequests }}</div>
                    <div class="card-subtitle-note color-slate">Requests awaiting admin review</div>
                </div>
                <!-- Smooth Gray Wave -->
                <div class="card-wave-box">
                    <svg viewBox="0 0 300 35" class="card-wave-svg" preserveAspectRatio="none">
                        <path d="M0,30 C80,26 160,14 220,18 C260,22 280,28 300,30" fill="none" stroke="#475569" stroke-width="1.8" />
                    </svg>
                </div>
            </div>

            <!-- 4. Approved Vehicle Requests -->
            <div class="dash-card">
                <div class="card-top-row">
                    <div class="card-badge-wrap">
                        <div class="card-icon-round bg-icon-green">
                            <svg style="width: 11px; height: 11px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <span class="card-title-text">Approved Vehicle Requests</span>
                    </div>
                    <span class="card-corner-icon color-green">✔</span>
                </div>
                <div class="card-mid-section">
                    <div class="card-number-bold">{{ $approvedRequests }}</div>
                    <div class="card-subtitle-note color-green">Requests approved and ticketed</div>
                </div>
                <!-- Smooth Green Wave -->
                <div class="card-wave-box">
                    <svg viewBox="0 0 300 35" class="card-wave-svg" preserveAspectRatio="none">
                        <path d="M0,30 C70,22 120,4 180,7 C240,10 270,26 300,30" fill="none" stroke="#10b981" stroke-width="2" />
                    </svg>
                </div>
            </div>

        </div>

        <!-- ROW 2: Three Wide Executive Cards -->
        <div class="row-grid-3">
            
            <!-- 1. Active Trips (On Trip) -->
            <div class="dash-card">
                <div class="card-top-row">
                    <div class="card-badge-wrap">
                        <div class="card-icon-round bg-icon-blue" style="font-weight: 800; font-size: 11px;">A</div>
                        <span class="card-title-text">Active Trips (On Trip)</span>
                    </div>
                    <span class="card-corner-icon" style="color: #38bdf8;">🚚</span>
                </div>
                <div class="card-mid-section">
                    <div class="card-number-bold">{{ $activeTrips }}</div>
                    <div class="card-subtitle-note color-blue">Trips currently on the road</div>
                </div>
                <!-- Smooth Blue Wave -->
                <div class="card-wave-box">
                    <svg viewBox="0 0 300 35" class="card-wave-svg" preserveAspectRatio="none">
                        <path d="M0,30 C80,28 140,11 200,16 C260,20 280,30 300,30" fill="none" stroke="#0284c7" stroke-width="2" />
                    </svg>
                </div>
            </div>

            <!-- 2. Pending Withdrawal Slips -->
            <div class="dash-card" style="padding-bottom: 8px;">
                <div class="card-top-row">
                    <div class="card-badge-wrap">
                        <div class="card-icon-round bg-icon-amber">
                            <svg style="width: 11px; height: 11px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <span class="card-title-text">Pending Withdrawal Slips</span>
                    </div>
                    <span class="card-corner-icon" style="color: #f59e0b;">💵</span>
                </div>
                <div class="card-mid-section">
                    <div class="card-number-bold">{{ $pendingSlips }}</div>
                    <div class="card-subtitle-note color-amber">Fuel slips awaiting approval</div>
                </div>
                <!-- Smooth Amber Wave -->
                <div style="margin: 4px -12px -8px -12px; height: 14px; overflow: hidden;">
                    <svg viewBox="0 0 300 20" style="width: 100%; height: 100%;" preserveAspectRatio="none">
                        <path d="M0,16 C80,14 160,4 240,7 L300,3" fill="none" stroke="#f59e0b" stroke-width="1.8" />
                    </svg>
                </div>
            </div>

            <!-- 3. This Month's Gas Expenses -->
            <div class="dash-card" style="padding-bottom: 8px;">
                <div class="card-top-row">
                    <div class="card-badge-wrap">
                        <div class="card-icon-round bg-icon-red">
                            <svg style="width: 11px; height: 11px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <span class="card-title-text">This Month's Gas Expenses</span>
                    </div>
                    <span class="card-corner-icon" style="color: #ef4444;">🔥</span>
                </div>
                <div class="card-mid-section">
                    <div class="card-number-bold">₱{{ number_format($gasExpenses['month'], 2) }}</div>
                    <div class="card-subtitle-note color-red">
                        Today: ₱{{ number_format($gasExpenses['today'], 2) }} | Week: ₱{{ number_format($gasExpenses['week'], 2) }}
                    </div>
                </div>
                <div style="width: 100%; height: 2px; background: rgba(239, 68, 68, 0.2); border-radius: 99px; margin-top: 6px; overflow: hidden;">
                    <div style="height: 100%; background: #ef4444; width: 100%;"></div>
                </div>
            </div>

        </div>

        <!-- ROW 3: Three Visual Analytics & Recent Activity Cards -->
        <div class="row-grid-bottom">
            
            <!-- Column 1: Trip Activity Area Chart -->
            <div class="dash-card dash-card-pad">
                <div class="panel-top-bar">
                    <div class="panel-title-text">
                        <span style="color: #0284c7;">📈</span>
                        <span>Trip Activity (This Week)</span>
                    </div>
                    <div class="panel-filter-btn">
                        This Week ⌵
                    </div>
                </div>

                <!-- Custom Area Chart with Grid & Values -->
                <div style="position: relative; width: 100%; height: 100px; display: flex; flex-direction: column; justify-content: space-between;">
                    
                    <!-- SVG Area Line with Grid -->
                    <div style="position: relative; width: 100%; height: 80px;">
                        <svg viewBox="0 0 350 140" style="width: 100%; height: 100%; display: block;" preserveAspectRatio="none">
                            <defs>
                                <linearGradient id="chartAreaGradientHD" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#0284c7" stop-opacity="0.35" />
                                    <stop offset="100%" stop-color="#0284c7" stop-opacity="0.0" />
                                </linearGradient>
                            </defs>
                            
                            <!-- Grid Lines -->
                            <line x1="0" y1="20" x2="350" y2="20" stroke="#161f30" stroke-width="1" />
                            <line x1="0" y1="55" x2="350" y2="55" stroke="#161f30" stroke-width="1" />
                            <line x1="0" y1="90" x2="350" y2="90" stroke="#161f30" stroke-width="1" />
                            <line x1="0" y1="125" x2="350" y2="125" stroke="#161f30" stroke-width="1" />
                            
                            <!-- Area Gradient Fill -->
                            <path d="M 0,130 Q 30,110 60,95 T 120,25 T 175,35 T 235,75 T 295,95 T 350,130 L 350,140 L 0,140 Z" fill="url(#chartAreaGradientHD)" />
                            
                            <!-- Blue Curved Line -->
                            <path d="M 0,130 Q 30,110 60,95 T 120,25 T 175,35 T 235,75 T 295,95 T 350,130" fill="none" stroke="#0284c7" stroke-width="2.5" />
                            
                            <!-- Data Point Circles -->
                            <circle cx="0" cy="130" r="2.5" fill="#ffffff" stroke="#0284c7" stroke-width="1.8" />
                            <circle cx="60" cy="95" r="3" fill="#ffffff" stroke="#0284c7" stroke-width="1.8" />
                            <circle cx="120" cy="25" r="3.5" fill="#ffffff" stroke="#0284c7" stroke-width="2" />
                            <circle cx="175" cy="35" r="3.5" fill="#ffffff" stroke="#0284c7" stroke-width="2" />
                            <circle cx="235" cy="75" r="3" fill="#ffffff" stroke="#0284c7" stroke-width="1.8" />
                            <circle cx="295" cy="95" r="2.5" fill="#ffffff" stroke="#0284c7" stroke-width="1.8" />
                            <circle cx="350" cy="130" r="2.5" fill="#ffffff" stroke="#0284c7" stroke-width="1.8" />
                        </svg>
                    </div>

                    <!-- Day Labels -->
                    <div style="display: flex; justify-content: space-between; font-size: 9.5px; color: #94a3b8;">
                        <span>Mon</span>
                        <span>Tue</span>
                        <span>Wed</span>
                        <span>Thu</span>
                        <span>Fri</span>
                        <span>Sat</span>
                        <span>Sun</span>
                    </div>
                </div>

                <div style="display: flex; align-items: center; justify-content: center; gap: 4px; font-size: 9.5px; color: #94a3b8; margin-top: 2px;">
                    <span style="width: 6px; height: 6px; border-radius: 2px; background: #0284c7; display: inline-block;"></span>
                    <span>Trips</span>
                </div>
            </div>

            <!-- Column 2: Vehicle Request Status Donut Chart -->
            <div class="dash-card dash-card-pad">
                <div class="panel-top-bar">
                    <div class="panel-title-text">
                        <span style="color: #10b981;">📉</span>
                        <span>Vehicle Request Status</span>
                    </div>
                </div>

                <!-- Donut Chart & Legend -->
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin: auto 0;">
                    
                    <!-- Fixed SVG Donut Ring -->
                    <div style="position: relative; width: 80px; height: 80px; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                        <svg viewBox="0 0 100 100" style="width: 80px; height: 80px; transform: rotate(-90deg); display: block;">
                            <!-- Background Track -->
                            <circle cx="50" cy="50" r="38" fill="none" stroke="#161f30" stroke-width="13" />
                            
                            <!-- Completed Segment (Orange) -->
                            <circle cx="50" cy="50" r="38" fill="none" stroke="#f59e0b" stroke-width="13"
                                stroke-dasharray="238.76"
                                stroke-dashoffset="{{ 238.76 - (238.76 * max(0.08, $statusBreakdown['completed_pct'] / 100)) }}" />
                            
                            <!-- Approved Segment (Sky Blue) -->
                            <circle cx="50" cy="50" r="38" fill="none" stroke="#0284c7" stroke-width="13"
                                stroke-dasharray="238.76"
                                stroke-dashoffset="{{ 238.76 - (238.76 * max(0.06, $statusBreakdown['approved_pct'] / 100)) }}"
                                transform="rotate({{ ($statusBreakdown['completed_pct'] / 100) * 360 }} 50 50)" />
                            
                            <!-- Pending Segment (Green) -->
                            <circle cx="50" cy="50" r="38" fill="none" stroke="#10b981" stroke-width="13"
                                stroke-dasharray="238.76"
                                stroke-dashoffset="{{ 238.76 - (238.76 * max(0.05, $statusBreakdown['pending_pct'] / 100)) }}"
                                transform="rotate({{ (($statusBreakdown['completed_pct'] + $statusBreakdown['approved_pct']) / 100) * 360 }} 50 50)" />
                            
                            <!-- Rejected Segment (Red) -->
                            <circle cx="50" cy="50" r="38" fill="none" stroke="#ef4444" stroke-width="13"
                                stroke-dasharray="238.76"
                                stroke-dashoffset="{{ 238.76 - (238.76 * max(0.04, $statusBreakdown['rejected_pct'] / 100)) }}"
                                transform="rotate({{ (($statusBreakdown['completed_pct'] + $statusBreakdown['approved_pct']) / 100) * 360 }} 50 50)" />
                        </svg>

                        <!-- Center Total Count -->
                        <div style="position: absolute; text-align: center; pointer-events: none;">
                            <div style="font-size: 8px; color: #94a3b8; font-weight: 600;">Total</div>
                            <div style="font-size: 15px; font-weight: 800; color: #ffffff; line-height: 1;">
                                {{ $statusBreakdown['total'] }}
                            </div>
                        </div>
                    </div>

                    <!-- Legend -->
                    <div style="display: flex; flex-direction: column; gap: 4px; flex-grow: 1; font-size: 9.5px;">
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div style="display: flex; align-items: center; gap: 4px;">
                                <span style="width: 6px; height: 6px; border-radius: 50%; background: #10b981; display: inline-block;"></span>
                                <span style="color: #cbd5e1; font-weight: 500;">Pending</span>
                            </div>
                            <span style="color: #ffffff; font-weight: 600;">{{ $statusBreakdown['pending'] }} <span style="color: #64748b; font-size: 8.5px;">({{ $statusBreakdown['pending_pct'] }}%)</span></span>
                        </div>

                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div style="display: flex; align-items: center; gap: 4px;">
                                <span style="width: 6px; height: 6px; border-radius: 50%; background: #0284c7; display: inline-block;"></span>
                                <span style="color: #cbd5e1; font-weight: 500;">Approved</span>
                            </div>
                            <span style="color: #ffffff; font-weight: 600;">{{ $statusBreakdown['approved'] }} <span style="color: #64748b; font-size: 8.5px;">({{ $statusBreakdown['approved_pct'] }}%)</span></span>
                        </div>

                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div style="display: flex; align-items: center; gap: 4px;">
                                <span style="width: 6px; height: 6px; border-radius: 50%; background: #f59e0b; display: inline-block;"></span>
                                <span style="color: #cbd5e1; font-weight: 500;">Completed</span>
                            </div>
                            <span style="color: #ffffff; font-weight: 600;">{{ $statusBreakdown['completed'] }} <span style="color: #64748b; font-size: 8.5px;">({{ $statusBreakdown['completed_pct'] }}%)</span></span>
                        </div>

                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div style="display: flex; align-items: center; gap: 4px;">
                                <span style="width: 6px; height: 6px; border-radius: 50%; background: #ef4444; display: inline-block;"></span>
                                <span style="color: #cbd5e1; font-weight: 500;">Rejected</span>
                            </div>
                            <span style="color: #ffffff; font-weight: 600;">{{ $statusBreakdown['rejected'] }} <span style="color: #64748b; font-size: 8.5px;">({{ $statusBreakdown['rejected_pct'] }}%)</span></span>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Column 3: Recent Vehicle Requests -->
            <div class="dash-card dash-card-pad">
                <div class="panel-top-bar">
                    <div class="panel-title-text">
                        <span>Recent Vehicle Requests</span>
                    </div>
                    <a href="/admin/vehicle-requests" class="panel-filter-btn">
                        View All
                    </a>
                </div>

                <div class="feed-list-box">
                    @forelse($recentRequests as $req)
                        @php
                            $dotColor = match($req->status) {
                                'pending' => '#ef4444',
                                'approved' => '#0284c7',
                                'completed' => '#10b981',
                                default => '#94a3b8',
                            };
                            $pillClass = match($req->status) {
                                'pending' => 'status-pill-pending',
                                'approved' => 'status-pill-approved',
                                'on_trip' => 'status-pill-approved',
                                'completed' => 'status-pill-completed',
                                'rejected' => 'status-pill-rejected',
                                default => 'status-pill-pending',
                            };
                            $statusLabel = match($req->status) {
                                'on_trip' => 'On Trip',
                                default => ucfirst($req->status),
                            };
                        @endphp
                        <div class="feed-item-row">
                            <div class="feed-item-left">
                                <span class="feed-dot-indicator" style="background: {{ $dotColor }};"></span>
                                <div class="feed-text-group">
                                    <span class="feed-ref-code">{{ $req->request_number }}</span>
                                    <span class="feed-purpose-desc">{{ $req->purpose ?? $req->destination ?? $req->employee_name }}</span>
                                </div>
                            </div>
                            <div class="feed-item-right">
                                <span class="feed-status-pill {{ $pillClass }}">{{ $statusLabel }}</span>
                                <span class="feed-timestamp">{{ $req->created_at ? $req->created_at->diffForHumans(null, true) : 'now' }}</span>
                            </div>
                        </div>
                    @empty
                        <div style="padding: 20px 0; text-align: center; color: #64748b; font-size: 10px;">
                            No vehicle requests found.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- 5. Bottom Copyright & Version -->
        <div class="dash-footer-row">
            <div>&copy; {{ date('Y') }} PeliCle. All rights reserved.</div>
            <div>v1.0.0</div>
        </div>

    </div>
</x-filament-panels::page>
