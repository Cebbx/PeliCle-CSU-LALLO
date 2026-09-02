<x-filament-panels::page>
    <style>
        /* Exact Theme Match from Reference UI */
        :root {
            --bg-dark-card: #0d121d;
            --bg-dark-card-inner: #121826;
            --border-card: #1e2638;
            --border-card-hover: #2b364e;
            --text-title: #ffffff;
            --text-subtitle: #94a3b8;
            --text-muted: #64748b;
            --color-green: #10b981;
            --color-blue: #0284c7;
            --color-sky: #0ea5e9;
            --color-amber: #f59e0b;
            --color-red: #ef4444;
        }

        .fi-page {
            background-color: transparent !important;
        }

        .dashboard-container {
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: #f8fafc;
            max-width: 100%;
        }

        /* 1. Header Banner */
        .header-section {
            margin-bottom: 24px;
        }
        .header-title {
            font-size: 32px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.02em;
            margin: 0;
            line-height: 1.1;
        }
        .header-greeting {
            font-size: 15px;
            font-weight: 700;
            color: #ffffff;
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .header-desc {
            font-size: 13px;
            color: #94a3b8;
            margin-top: 4px;
            line-height: 1.4;
        }
        .header-live-line {
            font-size: 12px;
            color: #64748b;
            margin-top: 4px;
            font-weight: 500;
        }

        /* 2. Layout Grids */
        .grid-row-1 {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 16px;
        }
        .grid-row-2 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 16px;
        }
        .grid-row-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1.25fr;
            gap: 16px;
        }

        @media (max-width: 1200px) {
            .grid-row-1 { grid-template-columns: repeat(2, 1fr); }
            .grid-row-2 { grid-template-columns: repeat(2, 1fr); }
            .grid-row-3 { grid-template-columns: 1fr; }
        }
        @media (max-width: 640px) {
            .grid-row-1 { grid-template-columns: 1fr; }
            .grid-row-2 { grid-template-columns: 1fr; }
        }

        /* 3. Stat Cards */
        .stat-box {
            background: #0d121d;
            border: 1px solid #1e2638;
            border-radius: 14px;
            padding: 18px 20px 0 20px;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 140px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.4);
            transition: border-color 0.2s;
        }
        .stat-box:hover {
            border-color: #2b364e;
        }

        .stat-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .stat-badge-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .stat-icon-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
        }
        .circle-green { background: #10b981; color: #ffffff; }
        .circle-blue { background: #0284c7; color: #ffffff; }
        .circle-sky { background: #0ea5e9; color: #ffffff; }
        .circle-amber { background: #f59e0b; color: #ffffff; }
        .circle-red { background: #ef4444; color: #ffffff; }

        .stat-title {
            font-size: 13px;
            font-weight: 600;
            color: #cbd5e1;
        }
        .stat-side-icon {
            font-size: 16px;
            opacity: 0.6;
        }

        .stat-mid {
            margin-top: 14px;
            margin-bottom: 6px;
        }
        .stat-big-num {
            font-size: 28px;
            font-weight: 800;
            color: #ffffff;
            line-height: 1;
            letter-spacing: -0.02em;
            display: flex;
            align-items: baseline;
            gap: 6px;
        }
        .stat-unit-text {
            font-size: 20px;
            font-weight: 700;
            color: #ffffff;
        }
        .stat-subtext {
            font-size: 11px;
            font-weight: 500;
            margin-top: 4px;
        }
        .text-green-accent { color: #10b981; }
        .text-blue-accent { color: #38bdf8; }
        .text-amber-accent { color: #fbbf24; }
        .text-red-accent { color: #f87171; }
        .text-slate-accent { color: #94a3b8; }

        /* Smooth Bottom Sparklines */
        .wave-bottom-wrapper {
            margin: 6px -20px 0 -20px;
            height: 32px;
            overflow: hidden;
            display: block;
        }
        .wave-svg {
            width: 100%;
            height: 100%;
            display: block;
        }

        /* 4. Bottom Analytics Cards */
        .panel-box {
            background: #0d121d;
            border: 1px solid #1e2638;
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.4);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 320px;
        }
        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }
        .panel-header-title {
            font-size: 14px;
            font-weight: 700;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .panel-header-btn {
            background: #141c2c;
            border: 1px solid #222e44;
            color: #cbd5e1;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.2s;
        }
        .panel-header-btn:hover {
            background: #1c273c;
            color: #ffffff;
        }

        /* Recent Requests List */
        .recent-list-container {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .recent-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #161f30;
            gap: 12px;
        }
        .recent-item:last-child {
            border-bottom: none;
        }
        .recent-left {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }
        .recent-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .recent-info {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }
        .recent-code {
            font-size: 12px;
            font-weight: 700;
            color: #ffffff;
            white-space: nowrap;
        }
        .recent-title {
            font-size: 12px;
            color: #94a3b8;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 170px;
        }
        .recent-right {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
        }
        .recent-pill {
            font-size: 11px;
            font-weight: 700;
            padding: 2px 10px;
            border-radius: 6px;
            display: inline-block;
            text-transform: capitalize;
        }
        .pill-pending { background: #f59e0b; color: #000000; }
        .pill-approved { background: #0284c7; color: #ffffff; }
        .pill-completed { background: #059669; color: #ffffff; }
        .pill-rejected { background: #dc2626; color: #ffffff; }

        .recent-time {
            font-size: 11px;
            color: #64748b;
            white-space: nowrap;
            min-width: 65px;
            text-align: right;
        }
    </style>

    <div class="dashboard-container">

        <!-- Top Welcome Header -->
        <div class="header-section">
            <h1 class="header-title">Dashboard</h1>
            <div class="header-greeting">
                Welcome back, {{ auth()->user()->name ?? 'Admin User' }}! 👋
            </div>
            <div class="header-desc">
                Here is the current operational status of the CSU Lal-lo Campus Vehicle & Trip Management System.
            </div>
            <div class="header-live-line">
                System Live • {{ \Carbon\Carbon::now('Asia/Manila')->format('h:i A') }}
            </div>
        </div>

        <!-- ROW 1: 4 Stat Cards -->
        <div class="grid-row-1">
            
            <!-- Card 1: Driver Availability -->
            <div class="stat-box">
                <div class="stat-top">
                    <div class="stat-badge-group">
                        <div class="stat-icon-circle circle-green">
                            <svg style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <span class="stat-title">Driver Availability</span>
                    </div>
                    <span class="stat-side-icon text-green-accent">👥</span>
                </div>
                <div class="stat-mid">
                    <div class="stat-big-num">
                        {{ $driverStats['available'] }} / {{ $driverStats['total'] }} <span class="stat-unit-text">Available</span>
                    </div>
                    <div class="stat-subtext text-green-accent">Drivers ready for dispatch</div>
                </div>
                <!-- Smooth Neon Green Wave -->
                <div class="wave-bottom-wrapper">
                    <svg viewBox="0 0 300 45" class="wave-svg" preserveAspectRatio="none">
                        <path d="M0,40 C70,38 120,8 180,12 C240,16 270,35 300,40" fill="none" stroke="#10b981" stroke-width="2.5" />
                    </svg>
                </div>
            </div>

            <!-- Card 2: Vehicle Availability -->
            <div class="stat-box">
                <div class="stat-top">
                    <div class="stat-badge-group">
                        <div class="stat-icon-circle circle-green">
                            <svg style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                        </div>
                        <span class="stat-title">Vehicle Availability</span>
                    </div>
                    <span class="stat-side-icon text-green-accent">🚚</span>
                </div>
                <div class="stat-mid">
                    <div class="stat-big-num">
                        {{ $vehicleStats['available'] }} / {{ $vehicleStats['total'] }} <span class="stat-unit-text">Available</span>
                    </div>
                    <div class="stat-subtext text-green-accent">Vehicles ready for dispatch</div>
                </div>
                <!-- Smooth Neon Green Wave -->
                <div class="wave-bottom-wrapper">
                    <svg viewBox="0 0 300 45" class="wave-svg" preserveAspectRatio="none">
                        <path d="M0,38 C60,42 120,6 180,14 C240,22 270,8 300,5" fill="none" stroke="#10b981" stroke-width="2.5" />
                    </svg>
                </div>
            </div>

            <!-- Card 3: Pending Vehicle Requests -->
            <div class="stat-box">
                <div class="stat-top">
                    <div class="stat-badge-group">
                        <div class="stat-icon-circle circle-blue">
                            <svg style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <span class="stat-title">Pending Vehicle Requests</span>
                    </div>
                    <span class="stat-side-icon" style="color: #64748b;">📄</span>
                </div>
                <div class="stat-mid">
                    <div class="stat-big-num">{{ $pendingRequests }}</div>
                    <div class="stat-subtext text-slate-accent">Requests awaiting admin review</div>
                </div>
                <!-- Smooth Gray Wave -->
                <div class="wave-bottom-wrapper">
                    <svg viewBox="0 0 300 45" class="wave-svg" preserveAspectRatio="none">
                        <path d="M0,42 C80,38 160,20 220,25 C260,30 280,40 300,42" fill="none" stroke="#475569" stroke-width="2" />
                    </svg>
                </div>
            </div>

            <!-- Card 4: Approved Vehicle Requests -->
            <div class="stat-box">
                <div class="stat-top">
                    <div class="stat-badge-group">
                        <div class="stat-icon-circle circle-green">
                            <svg style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <span class="stat-title">Approved Vehicle Requests</span>
                    </div>
                    <span class="stat-side-icon text-green-accent">✔</span>
                </div>
                <div class="stat-mid">
                    <div class="stat-big-num">{{ $approvedRequests }}</div>
                    <div class="stat-subtext text-green-accent">Requests approved and ticketed</div>
                </div>
                <!-- Smooth Green Wave -->
                <div class="wave-bottom-wrapper">
                    <svg viewBox="0 0 300 45" class="wave-svg" preserveAspectRatio="none">
                        <path d="M0,42 C70,32 120,6 180,10 C240,14 270,38 300,42" fill="none" stroke="#10b981" stroke-width="2.5" />
                    </svg>
                </div>
            </div>

        </div>

        <!-- ROW 2: 3 Wide Executive Cards -->
        <div class="grid-row-2">
            
            <!-- Card 1: Active Trips (On Trip) -->
            <div class="stat-box" style="padding-bottom: 0;">
                <div class="stat-top">
                    <div class="stat-badge-group">
                        <div class="stat-icon-circle circle-blue" style="font-weight: 800; font-size: 14px;">A</div>
                        <span class="stat-title">Active Trips (On Trip)</span>
                    </div>
                    <span class="stat-side-icon" style="color: #38bdf8;">🚚</span>
                </div>
                <div class="stat-mid">
                    <div class="stat-big-num">{{ $activeTrips }}</div>
                    <div class="stat-subtext text-blue-accent">Trips currently on the road</div>
                </div>
                <!-- Smooth Blue Wave -->
                <div class="wave-bottom-wrapper">
                    <svg viewBox="0 0 300 45" class="wave-svg" preserveAspectRatio="none">
                        <path d="M0,42 C80,40 140,16 200,22 C260,28 280,42 300,42" fill="none" stroke="#0284c7" stroke-width="2.5" />
                    </svg>
                </div>
            </div>

            <!-- Card 2: Pending Withdrawal Slips -->
            <div class="stat-box" style="padding-bottom: 20px;">
                <div class="stat-top">
                    <div class="stat-badge-group">
                        <div class="stat-icon-circle circle-amber">
                            <svg style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <span class="stat-title">Pending Withdrawal Slips</span>
                    </div>
                    <span class="stat-side-icon" style="color: #f59e0b;">💵</span>
                </div>
                <div class="stat-mid">
                    <div class="stat-big-num">{{ $pendingSlips }}</div>
                    <div class="stat-subtext text-amber-accent">Fuel slips awaiting approval</div>
                </div>
                <!-- Smooth Amber Wave at Bottom -->
                <div style="margin: 10px -20px -20px -20px; height: 20px; overflow: hidden;">
                    <svg viewBox="0 0 300 25" style="width: 100%; height: 100%;" preserveAspectRatio="none">
                        <path d="M0,22 C80,20 160,8 240,12 L300,5" fill="none" stroke="#f59e0b" stroke-width="2" />
                    </svg>
                </div>
            </div>

            <!-- Card 3: This Month's Gas Expenses -->
            <div class="stat-box" style="padding-bottom: 20px;">
                <div class="stat-top">
                    <div class="stat-badge-group">
                        <div class="stat-icon-circle circle-red">
                            <svg style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <span class="stat-title">This Month's Gas Expenses</span>
                    </div>
                    <span class="stat-side-icon" style="color: #ef4444;">🔥</span>
                </div>
                <div class="stat-mid">
                    <div class="stat-big-num">₱{{ number_format($gasExpenses['month'], 2) }}</div>
                    <div class="stat-subtext text-red-accent">
                        Today: ₱{{ number_format($gasExpenses['today'], 2) }} | Week: ₱{{ number_format($gasExpenses['week'], 2) }}
                    </div>
                </div>
                <div style="width: 100%; height: 3px; background: rgba(239, 68, 68, 0.2); border-radius: 99px; margin-top: 14px; overflow: hidden;">
                    <div style="height: 100%; background: #ef4444; width: 100%;"></div>
                </div>
            </div>

        </div>

        <!-- ROW 3: Three Visual Analytics & Recent Activity Cards -->
        <div class="grid-row-3">
            
            <!-- Column 1: Trip Activity Area Chart -->
            <div class="panel-box">
                <div class="panel-header">
                    <div class="panel-header-title">
                        <span style="color: #0284c7;">📈</span>
                        <span>Trip Activity (This Week)</span>
                    </div>
                    <div class="panel-header-btn">
                        This Week ⌵
                    </div>
                </div>

                <!-- Custom Area Chart with Grid & Values -->
                <div style="position: relative; width: 100%; height: 180px; display: flex; flex-direction: column; justify-content: space-between;">
                    
                    <!-- SVG Area Line with Grid -->
                    <div style="position: relative; width: 100%; height: 140px;">
                        <svg viewBox="0 0 350 140" style="width: 100%; height: 100%; display: block;" preserveAspectRatio="none">
                            <defs>
                                <linearGradient id="chartAreaGradient" x1="0" y1="0" x2="0" y2="1">
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
                            <path d="M 0,130 Q 30,110 60,95 T 120,25 T 175,35 T 235,75 T 295,95 T 350,130 L 350,140 L 0,140 Z" fill="url(#chartAreaGradient)" />
                            
                            <!-- Blue Curved Line -->
                            <path d="M 0,130 Q 30,110 60,95 T 120,25 T 175,35 T 235,75 T 295,95 T 350,130" fill="none" stroke="#0284c7" stroke-width="2.5" />
                            
                            <!-- Data Point Circles -->
                            <circle cx="0" cy="130" r="3" fill="#ffffff" stroke="#0284c7" stroke-width="2" />
                            <circle cx="60" cy="95" r="3.5" fill="#ffffff" stroke="#0284c7" stroke-width="2" />
                            <circle cx="120" cy="25" r="4.5" fill="#ffffff" stroke="#0284c7" stroke-width="2.5" />
                            <circle cx="175" cy="35" r="4" fill="#ffffff" stroke="#0284c7" stroke-width="2.5" />
                            <circle cx="235" cy="75" r="3.5" fill="#ffffff" stroke="#0284c7" stroke-width="2" />
                            <circle cx="295" cy="95" r="3" fill="#ffffff" stroke="#0284c7" stroke-width="2" />
                            <circle cx="350" cy="130" r="3" fill="#ffffff" stroke="#0284c7" stroke-width="2" />
                        </svg>
                    </div>

                    <!-- Day Labels -->
                    <div style="display: flex; justify-content: space-between; font-size: 11px; color: #94a3b8; padding-top: 4px;">
                        <span>Mon</span>
                        <span>Tue</span>
                        <span>Wed</span>
                        <span>Thu</span>
                        <span>Fri</span>
                        <span>Sat</span>
                        <span>Sun</span>
                    </div>
                </div>

                <div style="display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 11px; color: #94a3b8; margin-top: 8px;">
                    <span style="width: 8px; height: 8px; border-radius: 2px; background: #0284c7; display: inline-block;"></span>
                    <span>Trips</span>
                </div>
            </div>

            <!-- Column 2: Vehicle Request Status Donut Chart -->
            <div class="panel-box">
                <div class="panel-header">
                    <div class="panel-header-title">
                        <span style="color: #10b981;">📉</span>
                        <span>Vehicle Request Status</span>
                    </div>
                </div>

                <!-- Donut Chart & Legend -->
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px; margin: auto 0;">
                    
                    <!-- Fixed SVG Donut Ring with Exact Percentages -->
                    <div style="position: relative; width: 130px; height: 130px; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                        <svg viewBox="0 0 100 100" style="width: 130px; height: 130px; transform: rotate(-90deg); display: block;">
                            <!-- Background Track -->
                            <circle cx="50" cy="50" r="38" fill="none" stroke="#161f30" stroke-width="12" />
                            
                            <!-- Completed Segment (Orange) -->
                            <circle cx="50" cy="50" r="38" fill="none" stroke="#f59e0b" stroke-width="12"
                                stroke-dasharray="238.76"
                                stroke-dashoffset="{{ 238.76 - (238.76 * max(0.08, $statusBreakdown['completed_pct'] / 100)) }}" />
                            
                            <!-- Approved Segment (Sky Blue) -->
                            <circle cx="50" cy="50" r="38" fill="none" stroke="#0284c7" stroke-width="12"
                                stroke-dasharray="238.76"
                                stroke-dashoffset="{{ 238.76 - (238.76 * max(0.06, $statusBreakdown['approved_pct'] / 100)) }}"
                                transform="rotate({{ ($statusBreakdown['completed_pct'] / 100) * 360 }} 50 50)" />
                            
                            <!-- Pending Segment (Green) -->
                            <circle cx="50" cy="50" r="38" fill="none" stroke="#10b981" stroke-width="12"
                                stroke-dasharray="238.76"
                                stroke-dashoffset="{{ 238.76 - (238.76 * max(0.05, $statusBreakdown['pending_pct'] / 100)) }}"
                                transform="rotate({{ (($statusBreakdown['completed_pct'] + $statusBreakdown['approved_pct']) / 100) * 360 }} 50 50)" />
                            
                            <!-- Rejected Segment (Red) -->
                            <circle cx="50" cy="50" r="38" fill="none" stroke="#ef4444" stroke-width="12"
                                stroke-dasharray="238.76"
                                stroke-dashoffset="{{ 238.76 - (238.76 * max(0.04, $statusBreakdown['rejected_pct'] / 100)) }}"
                                transform="rotate({{ (($statusBreakdown['completed_pct'] + $statusBreakdown['approved_pct'] + $statusBreakdown['pending_pct']) / 100) * 360 }} 50 50)" />
                        </svg>

                        <!-- Center Total Count -->
                        <div style="position: absolute; text-align: center; pointer-events: none;">
                            <div style="font-size: 10px; color: #94a3b8; font-weight: 600;">Total</div>
                            <div style="font-size: 22px; font-weight: 800; color: #ffffff; line-height: 1.1;">
                                {{ $statusBreakdown['total'] }}
                            </div>
                        </div>
                    </div>

                    <!-- Legend -->
                    <div style="display: flex; flex-direction: column; gap: 8px; flex-grow: 1; font-size: 11px;">
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <span style="width: 8px; height: 8px; border-radius: 50%; background: #10b981; display: inline-block;"></span>
                                <span style="color: #cbd5e1; font-weight: 500;">Pending</span>
                            </div>
                            <span style="color: #ffffff; font-weight: 600;">{{ $statusBreakdown['pending'] }} <span style="color: #64748b; font-size: 10px;">({{ $statusBreakdown['pending_pct'] }}%)</span></span>
                        </div>

                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <span style="width: 8px; height: 8px; border-radius: 50%; background: #0284c7; display: inline-block;"></span>
                                <span style="color: #cbd5e1; font-weight: 500;">Approved</span>
                            </div>
                            <span style="color: #ffffff; font-weight: 600;">{{ $statusBreakdown['approved'] }} <span style="color: #64748b; font-size: 10px;">({{ $statusBreakdown['approved_pct'] }}%)</span></span>
                        </div>

                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <span style="width: 8px; height: 8px; border-radius: 50%; background: #f59e0b; display: inline-block;"></span>
                                <span style="color: #cbd5e1; font-weight: 500;">Completed</span>
                            </div>
                            <span style="color: #ffffff; font-weight: 600;">{{ $statusBreakdown['completed'] }} <span style="color: #64748b; font-size: 10px;">({{ $statusBreakdown['completed_pct'] }}%)</span></span>
                        </div>

                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <span style="width: 8px; height: 8px; border-radius: 50%; background: #ef4444; display: inline-block;"></span>
                                <span style="color: #cbd5e1; font-weight: 500;">Rejected</span>
                            </div>
                            <span style="color: #ffffff; font-weight: 600;">{{ $statusBreakdown['rejected'] }} <span style="color: #64748b; font-size: 10px;">({{ $statusBreakdown['rejected_pct'] }}%)</span></span>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Column 3: Recent Vehicle Requests -->
            <div class="panel-box">
                <div class="panel-header">
                    <div class="panel-header-title">
                        <span>Recent Vehicle Requests</span>
                    </div>
                    <a href="/admin/vehicle-requests" class="panel-header-btn">
                        View All
                    </a>
                </div>

                <div class="recent-list-container">
                    @forelse($recentRequests as $req)
                        @php
                            $dotColor = match($req->status) {
                                'pending' => '#ef4444',
                                'approved' => '#0284c7',
                                'completed' => '#10b981',
                                default => '#94a3b8',
                            };
                            $pillClass = match($req->status) {
                                'pending' => 'pill-pending',
                                'approved' => 'pill-approved',
                                'on_trip' => 'pill-approved',
                                'completed' => 'pill-completed',
                                'rejected' => 'pill-rejected',
                                default => 'pill-pending',
                            };
                            $statusLabel = match($req->status) {
                                'on_trip' => 'On Trip',
                                default => ucfirst($req->status),
                            };
                        @endphp
                        <div class="recent-item">
                            <div class="recent-left">
                                <span class="recent-dot" style="background: {{ $dotColor }};"></span>
                                <div class="recent-info">
                                    <span class="recent-code">{{ $req->request_number }}</span>
                                    <span class="recent-title">{{ $req->purpose ?? $req->destination ?? $req->employee_name }}</span>
                                </div>
                            </div>
                            <div class="recent-right">
                                <span class="recent-pill {{ $pillClass }}">{{ $statusLabel }}</span>
                                <span class="recent-time">{{ $req->created_at ? $req->created_at->diffForHumans(null, true) : 'now' }}</span>
                            </div>
                        </div>
                    @empty
                        <div style="padding: 40px 0; text-align: center; color: #64748b; font-size: 12px;">
                            No vehicle requests found.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>
</x-filament-panels::page>
