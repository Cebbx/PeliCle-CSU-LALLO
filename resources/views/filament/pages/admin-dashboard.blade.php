<x-filament-panels::page>
    <style>
        .admin-dashboard-root {
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: #f8fafc;
        }

        /* Top Header */
        .exec-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 20px;
        }
        .exec-header-title {
            font-size: 26px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.02em;
            margin: 0;
            line-height: 1.2;
        }
        .exec-header-sub {
            font-size: 13px;
            color: #94a3b8;
            margin-top: 4px;
        }
        .exec-live-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #111827;
            border: 1px solid #1f2937;
            padding: 6px 14px;
            border-radius: 9999px;
            font-size: 12px;
            color: #e2e8f0;
            font-weight: 500;
        }
        .exec-pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 8px #10b981;
        }

        /* Grids */
        .exec-grid-4 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 16px;
        }
        .exec-grid-3 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 16px;
            margin-bottom: 16px;
        }
        .exec-grid-bottom {
            display: grid;
            grid-template-columns: 1fr 1fr 1.2fr;
            gap: 16px;
        }
        @media (max-width: 1024px) {
            .exec-grid-bottom {
                grid-template-columns: 1fr;
            }
        }

        /* Executive Cards */
        .exec-card {
            background: #111827;
            border: 1px solid #1f2937;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.35);
            display: flex;
            flex-col: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
            transition: transform 0.2s ease, border-color 0.2s ease;
        }
        .exec-card:hover {
            transform: translateY(-2px);
            border-color: #374151;
        }
        .exec-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }
        .exec-card-title-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .exec-icon-box {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }
        .icon-green { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.25); }
        .icon-blue { background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.25); }
        .icon-amber { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.25); }
        .icon-red { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.25); }

        .exec-card-label {
            font-size: 13px;
            font-weight: 600;
            color: #cbd5e1;
        }
        .exec-card-value {
            font-size: 26px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.02em;
            line-height: 1.1;
        }
        .exec-card-value-unit {
            font-size: 20px;
            font-weight: 700;
            color: #e2e8f0;
        }
        .exec-card-subtext {
            font-size: 11px;
            font-weight: 500;
            margin-top: 4px;
        }
        .subtext-green { color: #34d399; }
        .subtext-blue { color: #60a5fa; }
        .subtext-amber { color: #fbbf24; }
        .subtext-gray { color: #94a3b8; }

        /* Bottom Sparklines */
        .exec-sparkline-wrap {
            margin: 12px -20px -20px -20px;
            height: 32px;
            overflow: hidden;
            display: block;
        }
        .exec-sparkline-svg {
            width: 100%;
            height: 100%;
            display: block;
        }

        /* Recent Requests Feed */
        .req-feed-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #1f2937;
            gap: 12px;
        }
        .req-feed-item:last-child {
            border-bottom: none;
        }
        .req-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .dot-pending { background: #ef4444; }
        .dot-approved { background: #0ea5e9; }
        .dot-completed { background: #10b981; }

        .req-id {
            font-size: 12px;
            font-weight: 700;
            color: #ffffff;
        }
        .req-purpose {
            font-size: 11px;
            color: #94a3b8;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 160px;
        }
        .req-badge {
            font-size: 10px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 6px;
            display: inline-block;
            text-transform: capitalize;
        }
        .badge-pending { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); }
        .badge-approved { background: rgba(14, 165, 233, 0.15); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.3); }
        .badge-completed { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }
        .badge-rejected { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); }
        
        .req-time {
            font-size: 10px;
            color: #64748b;
            white-space: nowrap;
        }
    </style>

    <div class="admin-dashboard-root">

        <!-- Top Header & Live Status -->
        <div class="exec-header">
            <div>
                <h1 class="exec-header-title">Dashboard</h1>
                <div class="exec-header-sub">
                    Welcome back, <strong style="color: #ffffff;">{{ auth()->user()->name ?? 'Admin User' }}</strong>! 👋<br>
                    Here is the current operational status of the CSU Lal-lo Campus Vehicle & Trip Management System.
                </div>
            </div>
            <div class="exec-live-pill">
                <div class="exec-pulse-dot"></div>
                <span>System Live • <strong>{{ \Carbon\Carbon::now('Asia/Manila')->format('h:i A') }}</strong></span>
            </div>
        </div>

        <!-- ROW 1: 4 Executive Status Cards -->
        <div class="exec-grid-4">
            
            <!-- 1. Driver Availability -->
            <div class="exec-card">
                <div class="exec-card-header">
                    <div class="exec-card-title-group">
                        <div class="exec-icon-box icon-green">👥</div>
                        <span class="exec-card-label">Driver Availability</span>
                    </div>
                    <span style="color: #059669; font-size: 16px;">👤</span>
                </div>
                <div>
                    <div class="exec-card-value">
                        {{ $driverStats['available'] }} / {{ $driverStats['total'] }} <span class="exec-card-value-unit">Available</span>
                    </div>
                    <div class="exec-card-subtext subtext-green">Drivers ready for dispatch</div>
                </div>
                <div class="exec-sparkline-wrap">
                    <svg viewBox="0 0 300 50" class="exec-sparkline-svg" preserveAspectRatio="none">
                        <path d="M0,45 C80,35 140,5 200,15 C260,25 280,10 300,5 L300,50 L0,50 Z" fill="rgba(16, 185, 129, 0.15)" />
                        <path d="M0,45 C80,35 140,5 200,15 C260,25 280,10 300,5" fill="none" stroke="#10b981" stroke-width="2.5" />
                    </svg>
                </div>
            </div>

            <!-- 2. Vehicle Availability -->
            <div class="exec-card">
                <div class="exec-card-header">
                    <div class="exec-card-title-group">
                        <div class="exec-icon-box icon-green">🚚</div>
                        <span class="exec-card-label">Vehicle Availability</span>
                    </div>
                    <span style="color: #059669; font-size: 16px;">🚐</span>
                </div>
                <div>
                    <div class="exec-card-value">
                        {{ $vehicleStats['available'] }} / {{ $vehicleStats['total'] }} <span class="exec-card-value-unit">Available</span>
                    </div>
                    <div class="exec-card-subtext subtext-green">Vehicles ready for dispatch</div>
                </div>
                <div class="exec-sparkline-wrap">
                    <svg viewBox="0 0 300 50" class="exec-sparkline-svg" preserveAspectRatio="none">
                        <path d="M0,40 C60,45 120,10 180,20 C240,30 270,10 300,5 L300,50 L0,50 Z" fill="rgba(16, 185, 129, 0.15)" />
                        <path d="M0,40 C60,45 120,10 180,20 C240,30 270,10 300,5" fill="none" stroke="#10b981" stroke-width="2.5" />
                    </svg>
                </div>
            </div>

            <!-- 3. Pending Vehicle Requests -->
            <div class="exec-card">
                <div class="exec-card-header">
                    <div class="exec-card-title-group">
                        <div class="exec-icon-box icon-blue">📋</div>
                        <span class="exec-card-label">Pending Vehicle Requests</span>
                    </div>
                    <span style="color: #64748b; font-size: 16px;">📄</span>
                </div>
                <div>
                    <div class="exec-card-value">{{ $pendingRequests }}</div>
                    <div class="exec-card-subtext subtext-gray">Requests awaiting admin review</div>
                </div>
                <div class="exec-sparkline-wrap">
                    <svg viewBox="0 0 300 50" class="exec-sparkline-svg" preserveAspectRatio="none">
                        <path d="M0,45 C80,40 160,25 220,30 C260,35 280,42 300,45 L300,50 L0,50 Z" fill="rgba(100, 116, 139, 0.12)" />
                        <path d="M0,45 C80,40 160,25 220,30 C260,35 280,42 300,45" fill="none" stroke="#64748b" stroke-width="2" />
                    </svg>
                </div>
            </div>

            <!-- 4. Approved Vehicle Requests -->
            <div class="exec-card">
                <div class="exec-card-header">
                    <div class="exec-card-title-group">
                        <div class="exec-icon-box icon-green">✅</div>
                        <span class="exec-card-label">Approved Vehicle Requests</span>
                    </div>
                    <span style="color: #10b981; font-size: 16px;">✔</span>
                </div>
                <div>
                    <div class="exec-card-value">{{ $approvedRequests }}</div>
                    <div class="exec-card-subtext subtext-green">Requests approved and ticketed</div>
                </div>
                <div class="exec-sparkline-wrap">
                    <svg viewBox="0 0 300 50" class="exec-sparkline-svg" preserveAspectRatio="none">
                        <path d="M0,45 C70,35 120,10 180,15 C240,20 270,40 300,45 L300,50 L0,50 Z" fill="rgba(16, 185, 129, 0.15)" />
                        <path d="M0,45 C70,35 120,10 180,15 C240,20 270,40 300,45" fill="none" stroke="#10b981" stroke-width="2.5" />
                    </svg>
                </div>
            </div>

        </div>

        <!-- ROW 2: 3 Wide Executive Cards -->
        <div class="exec-grid-3">
            
            <!-- 1. Active Trips (On Trip) -->
            <div class="exec-card">
                <div class="exec-card-header">
                    <div class="exec-card-title-group">
                        <div class="exec-icon-box icon-blue" style="font-weight: 800;">A</div>
                        <span class="exec-card-label">Active Trips (On Trip)</span>
                    </div>
                    <span style="color: #3b82f6; font-size: 16px;">🚚</span>
                </div>
                <div>
                    <div class="exec-card-value">{{ $activeTrips }}</div>
                    <div class="exec-card-subtext subtext-blue">Trips currently on the road</div>
                </div>
                <div class="exec-sparkline-wrap">
                    <svg viewBox="0 0 300 50" class="exec-sparkline-svg" preserveAspectRatio="none">
                        <path d="M0,45 C80,42 140,20 200,28 C260,35 280,45 300,45 L300,50 L0,50 Z" fill="rgba(59, 130, 246, 0.15)" />
                        <path d="M0,45 C80,42 140,20 200,28 C260,35 280,45 300,45" fill="none" stroke="#3b82f6" stroke-width="2.5" />
                    </svg>
                </div>
            </div>

            <!-- 2. Pending Withdrawal Slips -->
            <div class="exec-card">
                <div class="exec-card-header">
                    <div class="exec-card-title-group">
                        <div class="exec-icon-box icon-amber">📄</div>
                        <span class="exec-card-label">Pending Withdrawal Slips</span>
                    </div>
                    <span style="font-size: 16px;">💵</span>
                </div>
                <div>
                    <div class="exec-card-value">{{ $pendingSlips }}</div>
                    <div class="exec-card-subtext subtext-amber">Fuel slips awaiting approval</div>
                </div>
                <div style="width: 100%; height: 4px; background: rgba(245, 158, 11, 0.2); border-radius: 999px; margin-top: 16px; overflow: hidden;">
                    <div style="height: 100%; background: #f59e0b; width: {{ min(100, max(5, $pendingSlips * 15)) }}%;"></div>
                </div>
            </div>

            <!-- 3. This Month's Gas Expenses -->
            <div class="exec-card">
                <div class="exec-card-header">
                    <div class="exec-card-title-group">
                        <div class="exec-icon-box icon-red">⛽</div>
                        <span class="exec-card-label">This Month's Gas Expenses</span>
                    </div>
                    <span style="font-size: 16px;">🔥</span>
                </div>
                <div>
                    <div class="exec-card-value">₱{{ number_format($gasExpenses['month'], 2) }}</div>
                    <div class="exec-card-subtext subtext-gray">
                        Today: <span style="color: #e2e8f0;">₱{{ number_format($gasExpenses['today'], 2) }}</span> | Week: <span style="color: #e2e8f0;">₱{{ number_format($gasExpenses['week'], 2) }}</span>
                    </div>
                </div>
                <div style="width: 100%; height: 4px; background: rgba(239, 68, 68, 0.2); border-radius: 999px; margin-top: 16px; overflow: hidden;">
                    <div style="height: 100%; background: linear-gradient(90deg, #ef4444, #f43f5e); width: 100%;"></div>
                </div>
            </div>

        </div>

        <!-- ROW 3: Visual Analytics & Recent Requests List -->
        <div class="exec-grid-bottom">
            
            <!-- Column 1: Trip Activity Area Chart -->
            <div class="exec-card" style="min-height: 280px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="color: #38bdf8;">📈</span>
                        <strong style="font-size: 13px; color: #ffffff;">Trip Activity (This Week)</strong>
                    </div>
                    <div style="padding: 4px 10px; background: #1f2937; border-radius: 8px; font-size: 11px; color: #94a3b8;">
                        This Week ⌵
                    </div>
                </div>

                <!-- Smooth SVG Area Line Chart -->
                <div style="position: relative; width: 100%; height: 160px; margin: auto 0;">
                    <svg viewBox="0 0 350 140" style="width: 100%; height: 100%; display: block;" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="areaGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.45" />
                                <stop offset="100%" stop-color="#3b82f6" stop-opacity="0.0" />
                            </linearGradient>
                        </defs>
                        
                        <!-- Horizontal Grid Lines -->
                        <line x1="0" y1="35" x2="350" y2="35" stroke="#1f2937" stroke-width="1" stroke-dasharray="3,3" />
                        <line x1="0" y1="75" x2="350" y2="75" stroke="#1f2937" stroke-width="1" stroke-dasharray="3,3" />
                        <line x1="0" y1="115" x2="350" y2="115" stroke="#1f2937" stroke-width="1" stroke-dasharray="3,3" />
                        
                        <!-- Gradient Area -->
                        <path d="M 0,130 Q 30,120 60,95 T 120,35 T 175,40 T 235,80 T 295,100 T 350,130 L 350,140 L 0,140 Z" fill="url(#areaGrad)" />
                        
                        <!-- Smooth Line -->
                        <path d="M 0,130 Q 30,120 60,95 T 120,35 T 175,40 T 235,80 T 295,100 T 350,130" fill="none" stroke="#3b82f6" stroke-width="2.5" />
                        
                        <!-- Glowing Data Dots -->
                        <circle cx="0" cy="130" r="3" fill="#60a5fa" />
                        <circle cx="60" cy="95" r="3" fill="#60a5fa" />
                        <circle cx="120" cy="35" r="4.5" fill="#ffffff" stroke="#3b82f6" stroke-width="2" />
                        <circle cx="175" cy="40" r="4" fill="#ffffff" stroke="#3b82f6" stroke-width="2" />
                        <circle cx="235" cy="80" r="3" fill="#60a5fa" />
                        <circle cx="295" cy="100" r="3" fill="#60a5fa" />
                        <circle cx="350" cy="130" r="3" fill="#60a5fa" />
                    </svg>

                    <!-- Days Labels -->
                    <div style="display: flex; justify-content: space-between; font-size: 11px; color: #64748b; margin-top: 4px;">
                        <span>Mon</span>
                        <span>Tue</span>
                        <span style="color: #60a5fa; font-weight: 700;">Wed</span>
                        <span style="color: #60a5fa; font-weight: 700;">Thu</span>
                        <span>Fri</span>
                        <span>Sat</span>
                        <span>Sun</span>
                    </div>
                </div>

                <div style="display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 11px; color: #94a3b8; margin-top: 8px;">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #3b82f6; display: inline-block;"></span>
                    <span>Trips</span>
                </div>
            </div>

            <!-- Column 2: Vehicle Request Status Donut Chart -->
            <div class="exec-card" style="min-height: 280px;">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                    <span style="color: #34d399;">📉</span>
                    <strong style="font-size: 13px; color: #ffffff;">Vehicle Request Status</strong>
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px; margin: auto 0;">
                    
                    <!-- Fixed Dimensions Donut SVG -->
                    <div style="position: relative; width: 120px; height: 120px; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                        <svg viewBox="0 0 100 100" style="width: 120px; height: 120px; transform: rotate(-90deg); display: block;">
                            <!-- Background Ring -->
                            <circle cx="50" cy="50" r="38" fill="none" stroke="#1f2937" stroke-width="12" />
                            
                            <!-- Completed Segment (Orange) -->
                            <circle cx="50" cy="50" r="38" fill="none" stroke="#f59e0b" stroke-width="12"
                                stroke-dasharray="238.76"
                                stroke-dashoffset="{{ 238.76 - (238.76 * max(0.05, $statusBreakdown['completed_pct'] / 100)) }}" />
                            
                            <!-- Approved Segment (Sky Blue) -->
                            <circle cx="50" cy="50" r="38" fill="none" stroke="#0ea5e9" stroke-width="12"
                                stroke-dasharray="238.76"
                                stroke-dashoffset="{{ 238.76 - (238.76 * max(0.05, $statusBreakdown['approved_pct'] / 100)) }}"
                                transform="rotate({{ ($statusBreakdown['completed_pct'] / 100) * 360 }} 50 50)" />
                            
                            <!-- Pending Segment (Green) -->
                            <circle cx="50" cy="50" r="38" fill="none" stroke="#10b981" stroke-width="12"
                                stroke-dasharray="238.76"
                                stroke-dashoffset="{{ 238.76 - (238.76 * max(0.05, $statusBreakdown['pending_pct'] / 100)) }}"
                                transform="rotate({{ (($statusBreakdown['completed_pct'] + $statusBreakdown['approved_pct']) / 100) * 360 }} 50 50)" />
                            
                            <!-- Rejected Segment (Red) -->
                            <circle cx="50" cy="50" r="38" fill="none" stroke="#ef4444" stroke-width="12"
                                stroke-dasharray="238.76"
                                stroke-dashoffset="{{ 238.76 - (238.76 * max(0.03, $statusBreakdown['rejected_pct'] / 100)) }}"
                                transform="rotate({{ (($statusBreakdown['completed_pct'] + $statusBreakdown['approved_pct'] + $statusBreakdown['pending_pct']) / 100) * 360 }} 50 50)" />
                        </svg>

                        <!-- Center Total Count -->
                        <div style="position: absolute; text-align: center; pointer-events: none;">
                            <div style="font-size: 9px; color: #94a3b8; text-transform: uppercase; font-weight: 700;">Total</div>
                            <div style="font-size: 18px; font-weight: 900; color: #ffffff; line-height: 1;">
                                {{ $statusBreakdown['total'] }}
                            </div>
                        </div>
                    </div>

                    <!-- Legend -->
                    <div style="display: flex; flex-direction: column; gap: 8px; flex-grow: 1; font-size: 11px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <span style="width: 8px; height: 8px; border-radius: 50%; background: #10b981;"></span>
                                <span style="color: #cbd5e1;">Pending</span>
                            </div>
                            <span style="color: #94a3b8; font-weight: 600;">{{ $statusBreakdown['pending'] }} <span style="font-size: 9px; color: #64748b;">({{ $statusBreakdown['pending_pct'] }}%)</span></span>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <span style="width: 8px; height: 8px; border-radius: 50%; background: #0ea5e9;"></span>
                                <span style="color: #cbd5e1;">Approved</span>
                            </div>
                            <span style="color: #94a3b8; font-weight: 600;">{{ $statusBreakdown['approved'] }} <span style="font-size: 9px; color: #64748b;">({{ $statusBreakdown['approved_pct'] }}%)</span></span>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <span style="width: 8px; height: 8px; border-radius: 50%; background: #f59e0b;"></span>
                                <span style="color: #cbd5e1;">Completed</span>
                            </div>
                            <span style="color: #94a3b8; font-weight: 600;">{{ $statusBreakdown['completed'] }} <span style="font-size: 9px; color: #64748b;">({{ $statusBreakdown['completed_pct'] }}%)</span></span>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <span style="width: 8px; height: 8px; border-radius: 50%; background: #ef4444;"></span>
                                <span style="color: #cbd5e1;">Rejected</span>
                            </div>
                            <span style="color: #94a3b8; font-weight: 600;">{{ $statusBreakdown['rejected'] }} <span style="font-size: 9px; color: #64748b;">({{ $statusBreakdown['rejected_pct'] }}%)</span></span>
                        </div>
                    </div>

                </div>

                <div style="font-size: 10px; color: #64748b; text-align: center; border-top: 1px solid #1f2937; padding-top: 8px; margin-top: 8px;">
                    Department Distribution Ratio
                </div>
            </div>

            <!-- Column 3: Recent Vehicle Requests List -->
            <div class="exec-card" style="min-height: 280px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <strong style="font-size: 13px; color: #ffffff;">Recent Vehicle Requests</strong>
                    <a href="/admin/vehicle-requests" style="padding: 3px 8px; background: #1f2937; border-radius: 6px; font-size: 11px; color: #94a3b8; text-decoration: none;">
                        View All
                    </a>
                </div>

                <div style="display: flex; flex-direction: column;">
                    @forelse($recentRequests as $req)
                        @php
                            $dotClass = match($req->status) {
                                'pending' => 'dot-pending',
                                'approved' => 'dot-approved',
                                'completed' => 'dot-completed',
                                default => 'dot-pending',
                            };
                            $badgeClass = match($req->status) {
                                'pending' => 'badge-pending',
                                'approved' => 'badge-approved',
                                'on_trip' => 'badge-approved',
                                'completed' => 'badge-completed',
                                'rejected' => 'badge-rejected',
                                default => 'badge-pending',
                            };
                            $statusText = match($req->status) {
                                'on_trip' => 'On Trip',
                                default => ucfirst($req->status),
                            };
                        @endphp
                        <div class="req-feed-item">
                            <div style="display: flex; align-items: center; gap: 8px; min-width: 0;">
                                <div class="req-dot {{ $dotClass }}"></div>
                                <div style="min-width: 0;">
                                    <div class="req-id">{{ $req->request_number }}</div>
                                    <div class="req-purpose">{{ $req->purpose ?? $req->destination ?? $req->employee_name }}</div>
                                </div>
                            </div>

                            <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                                <span class="req-badge {{ $badgeClass }}">{{ $statusText }}</span>
                                <span class="req-time">{{ $req->created_at ? $req->created_at->diffForHumans(null, true) : 'now' }}</span>
                            </div>
                        </div>
                    @empty
                        <div style="padding: 30px 0; text-align: center; font-size: 12px; color: #64748b;">
                            No vehicle requests found.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>
</x-filament-panels::page>
