<x-filament-panels::page>
    <style>
        .fi-page {
            background-color: #070a11 !important;
        }
        .fi-main-ctn {
            max-width: 100% !important;
            padding: 0px 16px 8px 16px !important;
            background-color: #070a11 !important;
        }
        .fi-header {
            display: none !important;
        }
        *::-webkit-scrollbar {
            display: none !important;
            width: 0px !important;
            height: 0px !important;
        }
        * {
            -ms-overflow-style: none !important;
            scrollbar-width: none !important;
        }

        .dashboard-wrapper {
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: #f8fafc;
            width: 100%;
            background-color: #070a11;
        }

        /* 1. Header (Elevated & Compact) */
        .dash-header {
            margin-top: -6px;
            margin-bottom: 6px;
        }
        .dash-title {
            font-size: 28px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.02em;
            margin: 0;
            line-height: 0.95;
        }
        .dash-welcome {
            font-size: 15px;
            font-weight: 700;
            color: #ffffff;
            margin-top: 2px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .dash-desc {
            font-size: 12.5px;
            color: #94a3b8;
            margin-top: 1px;
            line-height: 1.2;
        }

        /* 2. Grid Rows */
        .row-grid-4 {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 12px;
        }
        .row-grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 12px;
        }
        .row-grid-bottom {
            display: grid;
            grid-template-columns: 1fr 1fr 1.25fr;
            gap: 12px;
        }

        @media (max-width: 1200px) {
            .row-grid-4 { grid-template-columns: repeat(2, 1fr); }
            .row-grid-3 { grid-template-columns: repeat(2, 1fr); }
            .row-grid-bottom { grid-template-columns: 1fr; }
        }
        @media (max-width: 640px) {
            .row-grid-4 { grid-template-columns: 1fr; }
            .row-grid-3 { grid-template-columns: 1fr; }
        }

        /* 3. Stat Cards (Enlarged, Prominent & Clickable) */
        .dash-card {
            background: #0d121d;
            border: 1px solid #1a2233;
            border-radius: 12px;
            padding: 12px 16px 0 16px;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 102px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.45);
        }
        .dash-card-pad {
            padding: 14px 16px;
            min-height: 210px;
        }
        .dash-card-link {
            text-decoration: none !important;
            cursor: pointer;
            transition: transform 0.16s ease, border-color 0.16s ease, box-shadow 0.16s ease;
        }
        .dash-card-link:hover {
            transform: translateY(-2px);
            border-color: rgba(234, 179, 8, 0.45) !important;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.5) !important;
        }

        .card-top-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-badge-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .card-icon-round {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            flex-shrink: 0;
        }
        .bg-icon-green { background: #10b981; color: #ffffff; }
        .bg-icon-blue { background: #0284c7; color: #ffffff; }
        .bg-icon-sky { background: #0ea5e9; color: #ffffff; }
        .bg-icon-amber { background: #f59e0b; color: #ffffff; }
        .bg-icon-red { background: #ef4444; color: #ffffff; }

        .card-title-text {
            font-size: 12.5px;
            font-weight: 600;
            color: #d1d5db;
        }
        .card-corner-icon {
            font-size: 15px;
            opacity: 0.5;
        }

        .card-mid-section {
            margin-top: 6px;
            margin-bottom: 2px;
        }
        .card-number-bold {
            font-size: 25px;
            font-weight: 800;
            color: #ffffff;
            line-height: 1;
            letter-spacing: -0.02em;
            display: flex;
            align-items: baseline;
            gap: 5px;
        }
        .card-unit-label {
            font-size: 17.5px;
            font-weight: 700;
            color: #ffffff;
        }
        .card-subtitle-note {
            font-size: 11px;
            font-weight: 500;
            margin-top: 3px;
        }
        .color-green { color: #10b981; }
        .color-blue { color: #38bdf8; }
        .color-amber { color: #fbbf24; }
        .color-red { color: #f87171; }
        .color-slate { color: #94a3b8; }

        /* Smooth Bottom Sparklines */
        .card-wave-box {
            margin: 4px -16px 0 -16px;
            height: 22px;
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
            margin-bottom: 8px;
        }
        .panel-title-text {
            font-size: 12px;
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
            padding: 2px 8px;
            border-radius: 5px;
            font-size: 9.5px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .panel-filter-btn:hover {
            background: #1e293b;
            color: #ffffff;
        }

        /* Recent Requests Feed (Compact Multi-Item & Clickable) */
        .feed-list-box {
            display: flex;
            flex-direction: column;
            gap: 0px;
        }
        .feed-item-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 2.5px 4px;
            border-bottom: 1px solid #161f30;
            gap: 6px;
        }
        .feed-item-row:last-child {
            border-bottom: none;
        }
        .feed-item-link {
            text-decoration: none !important;
            cursor: pointer;
            transition: background-color 0.15s ease;
            border-radius: 4px;
        }
        .feed-item-link:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }
        .feed-item-left {
            display: flex;
            align-items: center;
            gap: 5px;
            min-width: 0;
        }
        .feed-dot-indicator {
            width: 4.5px;
            height: 4.5px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .feed-text-group {
            display: flex;
            align-items: center;
            gap: 5px;
            min-width: 0;
        }
        .feed-ref-code {
            font-size: 10px;
            font-weight: 700;
            color: #ffffff;
            white-space: nowrap;
        }
        .feed-purpose-desc {
            font-size: 9.5px;
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
            font-size: 8.5px;
            font-weight: 700;
            padding: 1px 5px;
            border-radius: 3px;
            display: inline-block;
            text-transform: capitalize;
        }
        .status-pill-pending { background: #f59e0b; color: #000000; }
        .status-pill-approved { background: #0284c7; color: #ffffff; }
        .status-pill-completed { background: #059669; color: #ffffff; }
        .status-pill-rejected { background: #dc2626; color: #ffffff; }

        .feed-timestamp {
            font-size: 9px;
            color: #64748b;
            white-space: nowrap;
            min-width: 42px;
            text-align: right;
        }

        /* ========================================================
           Light Theme Dashboard Overrides (html:not(.dark))
           ======================================================== */
        html:not(.dark) .fi-page {
            background-color: #f1f5f9 !important;
        }
        html:not(.dark) .fi-main-ctn {
            background-color: #f1f5f9 !important;
        }
        html:not(.dark) .dashboard-wrapper {
            background-color: #f1f5f9 !important;
            color: #0f172a !important;
        }
        html:not(.dark) .dash-title {
            color: #0f172a !important;
        }
        html:not(.dark) .dash-welcome {
            color: #0f172a !important;
        }
        html:not(.dark) .dash-desc {
            color: #64748b !important;
        }
        html:not(.dark) .dash-card {
            background: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04) !important;
        }
        html:not(.dark) .dash-card-link:hover {
            border-color: rgba(217, 119, 6, 0.45) !important;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08) !important;
        }
        html:not(.dark) .card-title-text {
            color: #475569 !important;
        }
        html:not(.dark) .card-number-bold {
            color: #0f172a !important;
        }
        html:not(.dark) .card-unit-label {
            color: #0f172a !important;
        }
        html:not(.dark) .panel-title-text {
            color: #0f172a !important;
        }
        html:not(.dark) .panel-filter-btn {
            background: #f8fafc !important;
            border: 1px solid #cbd5e1 !important;
            color: #475569 !important;
        }
        html:not(.dark) .panel-filter-btn:hover {
            background: #e2e8f0 !important;
            color: #0f172a !important;
        }
        html:not(.dark) .feed-item-row {
            border-bottom: 1px solid #f1f5f9 !important;
        }
        html:not(.dark) .feed-item-link:hover {
            background-color: rgba(0, 0, 0, 0.04) !important;
        }
        html:not(.dark) .feed-ref-code {
            color: #0f172a !important;
        }
        html:not(.dark) .feed-purpose-desc {
            color: #64748b !important;
        }
        html:not(.dark) line {
            stroke: #e2e8f0 !important;
        }
        html:not(.dark) .donut-track {
            stroke: #e2e8f0 !important;
        }
        html:not(.dark) .donut-center-num {
            color: #0f172a !important;
        }
        html:not(.dark) .donut-legend-text {
            color: #334155 !important;
        }
        html:not(.dark) .donut-legend-val {
            color: #0f172a !important;
        }
    </style>

    <div class="dashboard-wrapper">

        <!-- Top Greeting Header -->
        <div class="dash-header">
            <h1 class="dash-title">Dashboard</h1>
            <div class="dash-welcome">
                Welcome back, {{ auth()->user()->name ?? 'Admin User' }}! 👋
            </div>
            <div class="dash-desc">
                Here is the current operational status of the CSU Lal-lo Campus Vehicle & Trip Management System.
            </div>
        </div>

        <!-- ROW 1: Four Main Fleet Status Cards (Clickable Links) -->
        <div class="row-grid-4">
            
            <!-- 1. Driver Availability (Links to Drivers) -->
            <a href="/admin/drivers" class="dash-card dash-card-link">
                <div class="card-top-row">
                    <div class="card-badge-wrap">
                        <div class="card-icon-round bg-icon-green">
                            <svg style="width: 12px; height: 12px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
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
                        <path d="M0,30 C70,28 120,6 180,10 C240,14 270,26 300,30" fill="none" stroke="#10b981" stroke-width="2" />
                    </svg>
                </div>
            </a>

            <!-- 2. Vehicle Availability (Links to Vehicles) -->
            <a href="/admin/vehicles" class="dash-card dash-card-link">
                <div class="card-top-row">
                    <div class="card-badge-wrap">
                        <div class="card-icon-round bg-icon-green">
                            <svg style="width: 12px; height: 12px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
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
            </a>

            <!-- 3. Pending Vehicle Requests (Links to Requests) -->
            <a href="/admin/vehicle-requests" class="dash-card dash-card-link">
                <div class="card-top-row">
                    <div class="card-badge-wrap">
                        <div class="card-icon-round bg-icon-blue">
                            <svg style="width: 12px; height: 12px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
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
            </a>

            <!-- 4. Approved Vehicle Requests (Links to Requests) -->
            <a href="/admin/vehicle-requests" class="dash-card dash-card-link">
                <div class="card-top-row">
                    <div class="card-badge-wrap">
                        <div class="card-icon-round bg-icon-green">
                            <svg style="width: 12px; height: 12px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
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
                        <path d="M0,30 C70,22 120,4 180,8 C240,12 270,26 300,30" fill="none" stroke="#10b981" stroke-width="2" />
                    </svg>
                </div>
            </a>

        </div>

        <!-- ROW 2: Three Wide Executive Cards (Clickable Links) -->
        <div class="row-grid-3">
            
            <!-- 1. Active Trips (Links to Trip Tickets) -->
            <a href="/admin/trip-tickets" class="dash-card dash-card-link">
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
            </a>

            <!-- 2. Pending Withdrawal Slips (Links to Slips) -->
            <a href="/admin/withdrawal-slips" class="dash-card dash-card-link" style="padding-bottom: 8px;">
                <div class="card-top-row">
                    <div class="card-badge-wrap">
                        <div class="card-icon-round bg-icon-amber">
                            <svg style="width: 12px; height: 12px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
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
                <div style="margin: 4px -16px -8px -16px; height: 16px; overflow: hidden;">
                    <svg viewBox="0 0 300 20" style="width: 100%; height: 100%;" preserveAspectRatio="none">
                        <path d="M0,16 C80,14 160,4 240,7 L300,3" fill="none" stroke="#f59e0b" stroke-width="1.8" />
                    </svg>
                </div>
            </a>

            <!-- 3. This Month's Gas Expenses (Links to Slips) -->
            <a href="/admin/withdrawal-slips" class="dash-card dash-card-link" style="padding-bottom: 8px;">
                <div class="card-top-row">
                    <div class="card-badge-wrap">
                        <div class="card-icon-round bg-icon-red">
                            <svg style="width: 12px; height: 12px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
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
            </a>

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
                    <a href="/admin/trip-tickets" class="panel-filter-btn">
                        View Trips
                    </a>
                </div>

                <!-- Custom Area Chart with Grid & Values -->
                <div style="position: relative; width: 100%; height: 110px; display: flex; flex-direction: column; justify-content: space-between;">
                    
                    <!-- SVG Area Line with Grid -->
                    <div style="position: relative; width: 100%; height: 85px;">
                        <svg viewBox="0 0 350 140" style="width: 100%; height: 100%; display: block;" preserveAspectRatio="none">
                            <defs>
                                <linearGradient id="chartAreaGradientHD3" x1="0" y1="0" x2="0" y2="1">
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
                            <path d="M 0,130 Q 30,110 60,95 T 120,25 T 175,35 T 235,75 T 295,95 T 350,130 L 350,140 L 0,140 Z" fill="url(#chartAreaGradientHD3)" />
                            
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
                    <div style="display: flex; justify-content: space-between; font-size: 10px; color: #94a3b8; padding-top: 2px;">
                        <span>Mon</span>
                        <span>Tue</span>
                        <span>Wed</span>
                        <span>Thu</span>
                        <span>Fri</span>
                        <span>Sat</span>
                        <span>Sun</span>
                    </div>
                </div>

                <div style="display: flex; align-items: center; justify-content: center; gap: 5px; font-size: 10px; color: #94a3b8; margin-top: 4px;">
                    <span style="width: 7px; height: 7px; border-radius: 2px; background: #0284c7; display: inline-block;"></span>
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
                    <a href="/admin/vehicle-requests" class="panel-filter-btn">
                        View Requests
                    </a>
                </div>

                <!-- Donut Chart & Legend -->
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; margin: auto 0;">
                    
                    <!-- Fixed SVG Donut Ring -->
                    <div style="position: relative; width: 90px; height: 90px; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                        <svg viewBox="0 0 100 100" style="width: 90px; height: 90px; transform: rotate(-90deg); display: block;">
                            <!-- Background Track -->
                            <circle class="donut-track" cx="50" cy="50" r="38" fill="none" stroke="#161f30" stroke-width="13" />
                            
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
                                transform="rotate({{ (($statusBreakdown['completed_pct'] + $statusBreakdown['approved_pct'] + $statusBreakdown['pending_pct']) / 100) * 360 }} 50 50)" />
                        </svg>

                        <!-- Center Total Count -->
                        <div style="position: absolute; text-align: center; pointer-events: none;">
                            <div style="font-size: 8.5px; color: #94a3b8; font-weight: 600;">Total</div>
                            <div class="donut-center-num" style="font-size: 17px; font-weight: 800; color: #ffffff; line-height: 1;">
                                {{ $statusBreakdown['total'] }}
                            </div>
                        </div>
                    </div>

                    <!-- Legend -->
                    <div style="display: flex; flex-direction: column; gap: 5px; flex-grow: 1; font-size: 10px;">
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div style="display: flex; align-items: center; gap: 4px;">
                                <span style="width: 6.5px; height: 6.5px; border-radius: 50%; background: #10b981; display: inline-block;"></span>
                                <span class="donut-legend-text" style="color: #cbd5e1; font-weight: 500;">Pending</span>
                            </div>
                            <span class="donut-legend-val" style="color: #ffffff; font-weight: 600;">{{ $statusBreakdown['pending'] }} <span style="color: #64748b; font-size: 9px;">({{ $statusBreakdown['pending_pct'] }}%)</span></span>
                        </div>

                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div style="display: flex; align-items: center; gap: 4px;">
                                <span style="width: 6.5px; height: 6.5px; border-radius: 50%; background: #0284c7; display: inline-block;"></span>
                                <span class="donut-legend-text" style="color: #cbd5e1; font-weight: 500;">Approved</span>
                            </div>
                            <span class="donut-legend-val" style="color: #ffffff; font-weight: 600;">{{ $statusBreakdown['approved'] }} <span style="color: #64748b; font-size: 9px;">({{ $statusBreakdown['approved_pct'] }}%)</span></span>
                        </div>

                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div style="display: flex; align-items: center; gap: 4px;">
                                <span style="width: 6.5px; height: 6.5px; border-radius: 50%; background: #f59e0b; display: inline-block;"></span>
                                <span class="donut-legend-text" style="color: #cbd5e1; font-weight: 500;">Completed</span>
                            </div>
                            <span class="donut-legend-val" style="color: #ffffff; font-weight: 600;">{{ $statusBreakdown['completed'] }} <span style="color: #64748b; font-size: 9px;">({{ $statusBreakdown['completed_pct'] }}%)</span></span>
                        </div>

                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div style="display: flex; align-items: center; gap: 4px;">
                                <span style="width: 6.5px; height: 6.5px; border-radius: 50%; background: #ef4444; display: inline-block;"></span>
                                <span class="donut-legend-text" style="color: #cbd5e1; font-weight: 500;">Rejected</span>
                            </div>
                            <span class="donut-legend-val" style="color: #ffffff; font-weight: 600;">{{ $statusBreakdown['rejected'] }} <span style="color: #64748b; font-size: 9px;">({{ $statusBreakdown['rejected_pct'] }}%)</span></span>
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
                        <a href="/admin/vehicle-requests" class="feed-item-row feed-item-link">
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
                        </a>
                    @empty
                        <div style="padding: 24px 0; text-align: center; color: #64748b; font-size: 11px;">
                            No vehicle requests found.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>
</x-filament-panels::page>
