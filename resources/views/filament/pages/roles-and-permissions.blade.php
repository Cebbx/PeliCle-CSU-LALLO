<x-filament-panels::page>
    <style>
        .rbac-container {
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: #f8fafc;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        /* 1. Header Banner */
        .rbac-header-card {
            background: linear-gradient(135deg, #0d1322 0%, #080c14 100%);
            border: 1px solid #1a2438;
            border-radius: 12px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.4);
        }
        .rbac-header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .rbac-shield-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(234, 179, 8, 0.12);
            border: 1px solid rgba(234, 179, 8, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #f59e0b;
            font-size: 20px;
            flex-shrink: 0;
        }
        .rbac-title {
            font-size: 17px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.01em;
            margin: 0;
        }
        .rbac-subtitle {
            font-size: 11.5px;
            color: #94a3b8;
            margin-top: 2px;
        }
        .rbac-stats-pills {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .rbac-stat-pill {
            background: #111827;
            border: 1px solid #1f2937;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            color: #cbd5e1;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .rbac-stat-num {
            font-weight: 800;
            color: #ffffff;
        }

        /* 2. Three Role Cards Grid */
        .rbac-roles-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
        }
        @media (max-width: 1024px) {
            .rbac-roles-grid {
                grid-template-columns: 1fr;
            }
        }
        .role-card {
            background: #0d121d;
            border: 1px solid #1a2233;
            border-radius: 12px;
            padding: 16px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }
        .role-card:hover {
            transform: translateY(-2px);
            border-color: rgba(234, 179, 8, 0.4);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.45);
        }
        .role-card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .role-badge-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .role-icon-box {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }
        .role-icon-admin { background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3); color: #f59e0b; }
        .role-icon-employee { background: rgba(2, 132, 199, 0.15); border: 1px solid rgba(2, 132, 199, 0.3); color: #38bdf8; }
        .role-icon-driver { background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: #10b981; }

        .role-name-text {
            font-size: 14px;
            font-weight: 800;
            color: #ffffff;
        }
        .role-tag-pill {
            font-size: 9.5px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .tag-admin { background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); }
        .tag-employee { background: rgba(2, 132, 199, 0.2); color: #38bdf8; border: 1px solid rgba(2, 132, 199, 0.3); }
        .tag-driver { background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }

        .role-desc {
            font-size: 11.5px;
            color: #94a3b8;
            line-height: 1.4;
            margin-bottom: 12px;
            min-height: 48px;
        }
        .role-capabilities-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-bottom: 14px;
        }
        .capability-pill {
            font-size: 10px;
            font-weight: 600;
            padding: 2px 6px;
            border-radius: 4px;
            background: #121826;
            color: #cbd5e1;
            border: 1px solid #1e293b;
        }
        .role-bottom-btn {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 7px 12px;
            background: #111827;
            border: 1px solid #1f2937;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            color: #ffffff;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .role-bottom-btn:hover {
            background: #1e293b;
            border-color: #334155;
            color: #fbbf24;
        }

        /* 3. Matrix Table Container */
        .rbac-matrix-card {
            background: #0d121d;
            border: 1px solid #1a2233;
            border-radius: 12px;
            padding: 16px 20px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.35);
        }
        .matrix-title-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }
        .matrix-title {
            font-size: 13.5px;
            font-weight: 800;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .matrix-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11.5px;
        }
        .matrix-table th {
            background: #080c14;
            color: #94a3b8;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 8px 10px;
            text-align: center;
            border-bottom: 1px solid #1a2233;
        }
        .matrix-table th:first-child {
            text-align: left;
            padding-left: 12px;
        }
        .matrix-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #141c2c;
            text-align: center;
            color: #cbd5e1;
        }
        .matrix-table td:first-child {
            text-align: left;
            font-weight: 600;
            color: #ffffff;
            padding-left: 12px;
        }
        .matrix-table tr:hover td {
            background: rgba(255, 255, 255, 0.02);
        }
        
        .perm-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 4px;
        }
        .perm-full { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }
        .perm-view { background: rgba(2, 132, 199, 0.15); color: #38bdf8; border: 1px solid rgba(2, 132, 199, 0.3); }
        .perm-none { background: rgba(100, 116, 139, 0.1); color: #64748b; border: 1px solid rgba(100, 116, 139, 0.2); }

        /* ========================================================
           Light Theme Overrides
           ======================================================== */
        html:not(.dark) .rbac-header-card {
            background: #ffffff !important;
            border-color: #e2e8f0 !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05) !important;
        }
        html:not(.dark) .rbac-title { color: #0f172a !important; }
        html:not(.dark) .rbac-subtitle { color: #64748b !important; }
        html:not(.dark) .rbac-stat-pill { background: #f8fafc !important; border-color: #e2e8f0 !important; color: #475569 !important; }
        html:not(.dark) .rbac-stat-num { color: #0f172a !important; }
        html:not(.dark) .role-card { background: #ffffff !important; border-color: #e2e8f0 !important; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04) !important; }
        html:not(.dark) .role-name-text { color: #0f172a !important; }
        html:not(.dark) .role-desc { color: #64748b !important; }
        html:not(.dark) .capability-pill { background: #f1f5f9 !important; color: #334155 !important; border-color: #cbd5e1 !important; }
        html:not(.dark) .role-bottom-btn { background: #f8fafc !important; border-color: #cbd5e1 !important; color: #0f172a !important; }
        html:not(.dark) .role-bottom-btn:hover { background: #e2e8f0 !important; color: #d97706 !important; }
        html:not(.dark) .rbac-matrix-card { background: #ffffff !important; border-color: #e2e8f0 !important; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04) !important; }
        html:not(.dark) .matrix-title { color: #0f172a !important; }
        html:not(.dark) .matrix-table th { background: #f8fafc !important; color: #64748b !important; border-color: #e2e8f0 !important; }
        html:not(.dark) .matrix-table td { border-color: #f1f5f9 !important; color: #334155 !important; }
        html:not(.dark) .matrix-table td:first-child { color: #0f172a !important; }
        html:not(.dark) .matrix-table tr:hover td { background: #f8fafc !important; }
    </style>

    <div class="rbac-container">
        
        <!-- 1. Executive RBAC Header -->
        <div class="rbac-header-card">
            <div class="rbac-header-left">
                <div class="rbac-shield-icon">
                    <svg style="width: 22px; height: 22px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div>
                    <h1 class="rbac-title">Roles & Access Control (RBAC)</h1>
                    <p class="rbac-subtitle">System privilege scope, authorization hierarchy, and portal clearance for CSU Lal-lo Campus.</p>
                </div>
            </div>

            <div class="rbac-stats-pills">
                <div class="rbac-stat-pill">
                    <span>👥 Total Accounts:</span>
                    <span class="rbac-stat-num">{{ $totalUsers ?? 0 }}</span>
                </div>
                <div class="rbac-stat-pill">
                    <span>👑 Admins:</span>
                    <span class="rbac-stat-num" style="color: #fbbf24;">{{ $adminCount ?? 0 }}</span>
                </div>
                <div class="rbac-stat-pill">
                    <span>👤 Employees:</span>
                    <span class="rbac-stat-num" style="color: #38bdf8;">{{ $employeeCount ?? 0 }}</span>
                </div>
                <div class="rbac-stat-pill">
                    <span>🚗 Drivers:</span>
                    <span class="rbac-stat-num" style="color: #34d399;">{{ $driverCount ?? 0 }}</span>
                </div>
            </div>
        </div>

        <!-- 2. Role Cards (Admin, Employee, Driver) -->
        <div class="rbac-roles-grid">
            
            <!-- Admin Role Card -->
            <div class="role-card">
                <div>
                    <div class="role-card-top">
                        <div class="role-badge-wrap">
                            <div class="role-icon-box role-icon-admin">👑</div>
                            <div>
                                <div class="role-name-text">Administrator</div>
                                <span style="font-size: 10px; color: #94a3b8;">Superuser Control</span>
                            </div>
                        </div>
                        <span class="role-tag-pill tag-admin">Full Access</span>
                    </div>
                    <div class="role-desc">
                        Full administrative oversight over dispatch operations, vehicle assignments, fuel approvals, analytics reports, and security audit logs.
                    </div>
                    <div class="role-capabilities-wrap">
                        <span class="capability-pill">Trip Dispatch</span>
                        <span class="capability-pill">Slips Approval</span>
                        <span class="capability-pill">Fleet Analytics</span>
                        <span class="capability-pill">Audit Trail</span>
                        <span class="capability-pill">User Control</span>
                    </div>
                </div>
                <a href="/admin/users" class="role-bottom-btn">
                    <span>View Admin Users ({{ $adminCount ?? 0 }})</span>
                    <span>→</span>
                </a>
            </div>

            <!-- Employee Role Card -->
            <div class="role-card">
                <div>
                    <div class="role-card-top">
                        <div class="role-badge-wrap">
                            <div class="role-icon-box role-icon-employee">👤</div>
                            <div>
                                <div class="role-name-text">Employee / Faculty</div>
                                <span style="font-size: 10px; color: #94a3b8;">Requester Portal</span>
                            </div>
                        </div>
                        <span class="role-tag-pill tag-employee">Requester</span>
                    </div>
                    <div class="role-desc">
                        Authorized university staff and faculty who submit official vehicle reservation requests, monitor trip approvals, and view travel orders.
                    </div>
                    <div class="role-capabilities-wrap">
                        <span class="capability-pill">Submit Requests</span>
                        <span class="capability-pill">Track Approvals</span>
                        <span class="capability-pill">Passenger List</span>
                        <span class="capability-pill">Print Travel Order</span>
                    </div>
                </div>
                <a href="/admin/users" class="role-bottom-btn">
                    <span>View Employees ({{ $employeeCount ?? 0 }})</span>
                    <span>→</span>
                </a>
            </div>

            <!-- Driver Role Card -->
            <div class="role-card">
                <div>
                    <div class="role-card-top">
                        <div class="role-badge-wrap">
                            <div class="role-icon-box role-icon-driver">🚗</div>
                            <div>
                                <div class="role-name-text">Campus Driver</div>
                                <span style="font-size: 10px; color: #94a3b8;">Fleet Operator</span>
                            </div>
                        </div>
                        <span class="role-tag-pill tag-driver">Operator</span>
                    </div>
                    <div class="role-desc">
                        Assigned vehicle operators who view dispatch tickets, request gasoline withdrawal slips, and present gate clearance QR codes to guards.
                    </div>
                    <div class="role-capabilities-wrap">
                        <span class="capability-pill">Driver Portal</span>
                        <span class="capability-pill">Active Trip Tickets</span>
                        <span class="capability-pill">Request Fuel Slip</span>
                        <span class="capability-pill">Gate QR Pass</span>
                    </div>
                </div>
                <a href="/admin/drivers" class="role-bottom-btn">
                    <span>View Drivers ({{ $driverCount ?? 0 }})</span>
                    <span>→</span>
                </a>
            </div>

        </div>

        <!-- 3. Permission Clearance Matrix Table -->
        <div class="rbac-matrix-card">
            <div class="matrix-title-bar">
                <div class="matrix-title">
                    <span style="color: #f59e0b;">📊</span>
                    <span>Module Authorization Matrix</span>
                </div>
                <span style="font-size: 10.5px; color: #94a3b8;">CSU Lal-lo Campus Security Policy v2.4</span>
            </div>

            <div style="overflow-x: auto;">
                <table class="matrix-table">
                    <thead>
                        <tr>
                            <th>System Module</th>
                            <th>👑 Administrator</th>
                            <th>👤 Employee</th>
                            <th>🚗 Driver</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Dashboard & Fleet Overview</td>
                            <td><span class="perm-badge perm-full">✔ Full Access</span></td>
                            <td><span class="perm-badge perm-view">👁️ Personal Stats</span></td>
                            <td><span class="perm-badge perm-view">👁️ Driver Stats</span></td>
                        </tr>
                        <tr>
                            <td>Vehicle Requests (Reservations)</td>
                            <td><span class="perm-badge perm-full">✔ Full / Approve / Reject</span></td>
                            <td><span class="perm-badge perm-view">✔ Create & Track Own</span></td>
                            <td><span class="perm-badge perm-none">🔒 No Access</span></td>
                        </tr>
                        <tr>
                            <td>Trip Tickets & Travel Orders</td>
                            <td><span class="perm-badge perm-full">✔ Create / Issue / Print</span></td>
                            <td><span class="perm-badge perm-view">👁️ View & Print Own</span></td>
                            <td><span class="perm-badge perm-view">👁️ View Assigned Tickets</span></td>
                        </tr>
                        <tr>
                            <td>Gasoline Withdrawal Slips</td>
                            <td><span class="perm-badge perm-full">✔ Full / Review & Approve</span></td>
                            <td><span class="perm-badge perm-none">🔒 No Access</span></td>
                            <td><span class="perm-badge perm-view">✔ Request Fuel Slip</span></td>
                        </tr>
                        <tr>
                            <td>Vehicles Fleet & Maintenance</td>
                            <td><span class="perm-badge perm-full">✔ Full Management</span></td>
                            <td><span class="perm-badge perm-view">👁️ View Availability</span></td>
                            <td><span class="perm-badge perm-view">👁️ View Assigned Vehicle</span></td>
                        </tr>
                        <tr>
                            <td>Drivers Roster & Assignments</td>
                            <td><span class="perm-badge perm-full">✔ Full Management</span></td>
                            <td><span class="perm-badge perm-none">🔒 No Access</span></td>
                            <td><span class="perm-badge perm-view">👁️ View Own Profile</span></td>
                        </tr>
                        <tr>
                            <td>Gate Security QR Scanner</td>
                            <td><span class="perm-badge perm-full">✔ Oversee & Complete</span></td>
                            <td><span class="perm-badge perm-none">🔒 No Access</span></td>
                            <td><span class="perm-badge perm-view">✔ Present QR Code</span></td>
                        </tr>
                        <tr>
                            <td>Executive Analytics & Reports</td>
                            <td><span class="perm-badge perm-full">✔ Full Export & Print</span></td>
                            <td><span class="perm-badge perm-none">🔒 No Access</span></td>
                            <td><span class="perm-badge perm-none">🔒 No Access</span></td>
                        </tr>
                        <tr>
                            <td>User Accounts & Role Settings</td>
                            <td><span class="perm-badge perm-full">✔ Full Management</span></td>
                            <td><span class="perm-badge perm-none">🔒 No Access</span></td>
                            <td><span class="perm-badge perm-none">🔒 No Access</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-filament-panels::page>
