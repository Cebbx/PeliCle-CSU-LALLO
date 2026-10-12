<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fleet Analytics Summary Report - {{ date('F Y') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        body {
            font-family: 'Arial', 'Calibri', sans-serif;
            background-color: #f3f4f6;
            color: black;
        }
        @media print {
            html, body {
                display: block !important;
                background: white !important;
                color: black !important;
                font-size: 11px;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                height: auto !important;
                min-height: 0 !important;
                overflow: visible !important;
            }
            .no-print {
                display: none !important;
            }
            .overflow-x-auto {
                overflow: visible !important;
                display: block !important;
                width: 100% !important;
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            @page {
                size: letter portrait;
                margin: 0.3in 0.35in;
            }
            .print-container {
                padding: 0 !important;
                margin: 0 !important;
                min-height: auto !important;
                min-width: 0 !important;
                height: auto !important;
                box-shadow: none !important;
                border: none !important;
                max-width: 100% !important;
                width: 100% !important;
                overflow: visible !important;
            }
            .page-break-avoid {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            table {
                page-break-inside: auto;
            }
            tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            thead {
                display: table-header-group !important;
            }
        }
    </style>
</head>
@php
    $backUrl = url()->previous();
    if ($backUrl === url()->current() || empty($backUrl)) {
        $backUrl = url('/admin/analytics');
    }
@endphp
<body class="bg-gray-100 py-3 sm:py-5 px-2 sm:px-4">

    <!-- In-App Browser (Messenger / IG) Notification Banner -->
    <div id="inAppNotice" class="no-print hidden max-w-[8.5in] mx-auto mb-3 bg-amber-50 border border-amber-300 rounded-xl p-3 sm:p-3.5 shadow-sm text-xs text-amber-900 flex items-start justify-between gap-3">
        <div class="flex items-start gap-2.5">
            <span class="text-base leading-none">📱</span>
            <div class="leading-relaxed">
                <strong class="font-bold">Nasa loob ka ng Messenger / In-App browser:</strong>
                Maaaring hindi gumana ang Print o Download dito dahil hinarang ito ng app. Pindutin ang <strong>3 dots (⋮ o •••)</strong> sa kanang itaas ng screen at piliin ang <strong>"Open in Chrome" / "Open in Browser"</strong>.
            </div>
        </div>
        <button type="button" onclick="document.getElementById('inAppNotice').classList.add('hidden')" class="text-amber-700 hover:text-amber-900 font-bold p-1 cursor-pointer">✕</button>
    </div>

    <!-- Top Action Bar (hidden on print) -->
    <div class="no-print max-w-[8.5in] mx-auto mb-3 flex flex-col md:flex-row items-stretch md:items-center justify-between bg-white p-3 rounded-xl shadow-md border border-gray-200 gap-3">
        <div class="flex items-center gap-2.5">
            <a href="{{ $backUrl }}" onclick="handleBack(event)" class="bg-gray-100 hover:bg-gray-200 active:bg-gray-300 text-gray-700 font-bold py-2 px-3 rounded-lg transition-colors flex items-center gap-1.5 text-xs border border-gray-300 shadow-sm cursor-pointer shrink-0" title="Go Back">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                <span>Back</span>
            </a>
            <div class="min-w-0">
                <h1 class="text-gray-800 font-bold text-xs sm:text-sm truncate">Fleet Analytics & Dispatch Summary Report</h1>
                <p class="text-[10px] text-gray-500 font-mono truncate">Generated: {{ \Carbon\Carbon::now('Asia/Manila')->format('F d, Y - h:i A') }}</p>
            </div>
        </div>

        <!-- Mode Toggle Pill: 1-Page Executive vs Full Detailed Log -->
        <div class="flex items-center bg-gray-100 p-1 rounded-lg border border-gray-300 text-xs font-semibold self-center sm:self-auto">
            <button type="button" id="modeExecutiveBtn" onclick="setReportMode('executive')" class="px-2.5 py-1.5 rounded-md bg-white text-gray-900 shadow-sm border border-gray-200 font-bold flex items-center gap-1 transition-all cursor-pointer">
                <span>📄</span>
                <span>1-Page Executive (1 Bond Paper)</span>
            </button>
            <button type="button" id="modeFullBtn" onclick="setReportMode('full')" class="px-2.5 py-1.5 rounded-md text-gray-600 hover:text-gray-900 font-medium flex items-center gap-1 transition-all cursor-pointer">
                <span>📋</span>
                <span>Full Detailed Log (All Trips)</span>
            </button>
        </div>

        <div class="flex items-center gap-2">
            <button id="downloadPdfBtn" type="button" onclick="downloadPDF()" class="flex-1 sm:flex-initial bg-green-600 hover:bg-green-700 active:bg-green-800 text-white font-bold py-2 px-3 rounded-lg transition-colors flex items-center justify-center gap-1.5 text-xs shadow-sm cursor-pointer disabled:opacity-50">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span id="downloadBtnText">Download PDF</span>
            </button>
            <button id="printDocBtn" type="button" onclick="triggerPrint()" class="flex-1 sm:flex-initial bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white font-bold py-2 px-3 rounded-lg transition-colors flex items-center justify-center gap-1.5 text-xs shadow-sm cursor-pointer">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Print Report</span>
            </button>
        </div>
    </div>

    <!-- Wrapper for smooth mobile horizontal scroll -->
    <div class="overflow-x-auto w-full max-w-[8.5in] mx-auto pb-4">
        <!-- Main Printable Sheet -->
        <div class="print-container max-w-[8.5in] mx-auto bg-white p-5 sm:p-7 border border-gray-300 shadow-lg text-black" style="min-width: 720px;">
        
            <!-- Header -->
            <div class="border-b-2 border-black pb-2.5 mb-2.5">
                <div class="flex items-center justify-between gap-3">
                    <img src="{{ asset('csu-logo.png') }}" class="w-16 h-16 object-contain" alt="CSU Logo" />
                    <div class="text-center flex-1">
                        <p class="text-[9px] tracking-widest uppercase font-semibold text-gray-700">Republic of the Philippines</p>
                        <h2 class="text-sm font-extrabold text-black uppercase tracking-wide">Cagayan State University</h2>
                        <p class="text-[11px] font-bold text-gray-800">Lal-lo Campus</p>
                        <p class="text-[10px] font-semibold text-gray-700 uppercase">GENERAL SERVICES OFFICE (GSO)</p>
                        <h3 class="text-xs font-black uppercase text-black mt-1 underline tracking-wider">
                            FLEET DISPATCH & VEHICLE UTILIZATION REPORT
                        </h3>
                    </div>
                    <div class="w-16 text-right font-mono text-[8px] text-gray-600">
                        <div>F-GSO-ANL</div>
                        <div>Rev. 02</div>
                    </div>
                </div>
            </div>

            <!-- Filter & Document Meta Bar -->
            <div class="grid grid-cols-4 gap-1.5 bg-gray-50 border border-black p-2 mb-2.5 text-[10px]">
                <div>
                    <span class="font-bold text-gray-700">Period:</span>
                    <span class="font-semibold text-black">
                        @if($startDate && $endDate)
                            {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                        @elseif($startDate)
                            From {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }}
                        @else
                            All Recorded Periods
                        @endif
                    </span>
                </div>
                <div>
                    <span class="font-bold text-gray-700">Department Scope:</span>
                    <span class="font-semibold text-black truncate">{{ $department ?: 'All Departments' }}</span>
                </div>
                <div>
                    <span class="font-bold text-gray-700">Trip Status:</span>
                    <span class="font-semibold text-black">{{ $status ? ucfirst($status) : 'All Statuses' }}</span>
                </div>
                <div class="text-right">
                    <span class="font-bold text-gray-700">Report Format:</span>
                    <span id="reportModeBadge" class="font-mono font-bold text-black bg-gray-200 px-1 py-0.5 rounded text-[9px]">1-Page Executive Summary</span>
                </div>
            </div>

            <!-- Executive KPI Highlights (4 Compact Cards in 1 Row) -->
            <div class="grid grid-cols-4 gap-2 mb-2.5 text-center">
                <div class="border border-black p-1.5 bg-gray-50 rounded-sm">
                    <div class="text-[8.5px] uppercase font-bold text-gray-600">Total Bookings</div>
                    <div class="text-base font-black text-black leading-tight">{{ $totalRequests }}</div>
                    <div class="text-[8px] text-gray-600 font-semibold">{{ $approvedCount }} Approved / Dispatched</div>
                </div>
                <div class="border border-black p-1.5 bg-gray-50 rounded-sm">
                    <div class="text-[8.5px] uppercase font-bold text-gray-600">Approval Rate</div>
                    <div class="text-base font-black text-emerald-800 leading-tight">{{ $approvalRate }}%</div>
                    <div class="text-[8px] text-gray-600 font-semibold">{{ $rejectedCount }} Disapproved / Void</div>
                </div>
                <div class="border border-black p-1.5 bg-gray-50 rounded-sm">
                    <div class="text-[8.5px] uppercase font-bold text-gray-600">Passengers Transported</div>
                    <div class="text-base font-black text-blue-900 leading-tight">{{ $totalPassengers }}</div>
                    <div class="text-[8px] text-gray-600 font-semibold">Authorized Delegates</div>
                </div>
                <div class="border border-black p-1.5 bg-gray-50 rounded-sm">
                    <div class="text-[8.5px] uppercase font-bold text-gray-600">Active Fleet Units</div>
                    <div class="text-base font-black text-black leading-tight">{{ $activeVehiclesCount ?? count($vehicleBreakdown) }}</div>
                    <div class="text-[8px] text-gray-600 font-semibold">University Service Assets</div>
                </div>
            </div>

            <!-- Section 1 & 2: 2-Column Grid (Department Demand Share + Vehicle Utilization) -->
            <div class="grid grid-cols-2 gap-2.5 mb-2.5">
                <!-- Left: Department Demand Distribution -->
                <div class="border border-black p-2 bg-white">
                    <h4 class="text-[10px] font-bold uppercase tracking-wider mb-1 text-black border-b border-black pb-0.5 flex justify-between">
                        <span>1. Department Trip Demand</span>
                        <span class="text-[8.5px] font-mono text-gray-600">Ranked by Volume</span>
                    </h4>
                    <table class="w-full border-collapse border border-black text-[9px]">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border border-black p-1 text-left font-bold">Office / College Unit</th>
                                <th class="border border-black p-1 text-center font-bold">Requests</th>
                                <th class="border border-black p-1 text-center font-bold">Demand Share</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $topDepts = $deptBreakdown->take(5);
                                $otherDeptsCount = $deptBreakdown->skip(5)->sum();
                            @endphp
                            @forelse($topDepts as $deptName => $count)
                                @php
                                    $percentage = $totalRequests > 0 ? round(($count / $totalRequests) * 100, 1) : 0;
                                @endphp
                                <tr>
                                    <td class="border border-black p-1 font-semibold truncate max-w-[150px]">{{ $deptName }}</td>
                                    <td class="border border-black p-1 text-center">{{ $count }}</td>
                                    <td class="border border-black p-1 text-center font-mono">{{ $percentage }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="border border-black p-1 text-center text-gray-500 italic">No departmental records.</td>
                                </tr>
                            @endforelse
                            @if($otherDeptsCount > 0)
                                @php
                                    $otherPct = $totalRequests > 0 ? round(($otherDeptsCount / $totalRequests) * 100, 1) : 0;
                                @endphp
                                <tr class="bg-gray-50 font-semibold">
                                    <td class="border border-black p-1 text-gray-700 italic">Other University Units ({{ $deptBreakdown->count() - 5 }})</td>
                                    <td class="border border-black p-1 text-center">{{ $otherDeptsCount }}</td>
                                    <td class="border border-black p-1 text-center font-mono">{{ $otherPct }}%</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <!-- Right: Fleet Vehicle Utilization -->
                <div class="border border-black p-2 bg-white">
                    <h4 class="text-[10px] font-bold uppercase tracking-wider mb-1 text-black border-b border-black pb-0.5 flex justify-between">
                        <span>2. Fleet Vehicle Utilization</span>
                        <span class="text-[8.5px] font-mono text-gray-600">Fleet Units ({{ count($vehicleBreakdown) }})</span>
                    </h4>
                    <table class="w-full border-collapse border border-black text-[9px]">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border border-black p-1 text-left font-bold">Vehicle Unit & Plate No.</th>
                                <th class="border border-black p-1 text-center font-bold">Bookings</th>
                                <th class="border border-black p-1 text-center font-bold">Fleet Share</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalVehicleTrips = array_sum($vehicleBreakdown);
                            @endphp
                            @forelse($vehicleBreakdown as $vehName => $count)
                                @php
                                    $vehPct = $totalVehicleTrips > 0 ? round(($count / $totalVehicleTrips) * 100, 1) : 0;
                                @endphp
                                <tr>
                                    <td class="border border-black p-1 font-semibold">{{ $vehName }}</td>
                                    <td class="border border-black p-1 text-center">{{ $count }} trip(s)</td>
                                    <td class="border border-black p-1 text-center font-mono">{{ $vehPct }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="border border-black p-1 text-center text-gray-500 italic">No fleet vehicle records.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- MODE 1: 1-Page Executive Summary (Recent Major Accomplished Dispatches - Fits 1 Bond Paper) -->
            <div id="executiveTripSummarySection" class="mb-3">
                <h4 class="text-[10px] font-bold uppercase tracking-wider mb-1 text-black border-b border-black pb-0.5 flex justify-between items-center">
                    <span>3. Key Accomplished Dispatch Logs (Executive Summary)</span>
                    <span class="text-[8.5px] font-mono text-gray-600">Top Highlights • {{ min(6, $requests->count()) }} of {{ $requests->count() }} records</span>
                </h4>
                <table class="w-full border-collapse border border-black text-[8.5px]">
                    <thead>
                        <tr class="bg-gray-100 font-bold">
                            <th class="border border-black p-1 text-center w-16">Req #</th>
                            <th class="border border-black p-1 text-left">Requester & Department</th>
                            <th class="border border-black p-1 text-left">Destination & Official Purpose</th>
                            <th class="border border-black p-1 text-center w-20">Travel Date</th>
                            <th class="border border-black p-1 text-left w-28">Assigned Vehicle</th>
                            <th class="border border-black p-1 text-center w-16">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests->take(6) as $req)
                            <tr>
                                <td class="border border-black p-1 text-center font-mono font-bold">{{ $req->request_number }}</td>
                                <td class="border border-black p-1">
                                    <div class="font-bold text-black">{{ $req->employee_name }}</div>
                                    <div class="text-[7.5px] text-gray-600">{{ $req->department }}</div>
                                </td>
                                <td class="border border-black p-1">
                                    <div class="font-semibold truncate max-w-[220px]">{{ $req->destination }}</div>
                                    <div class="text-[7.5px] text-gray-600 truncate max-w-[220px]">{{ $req->purpose }}</div>
                                </td>
                                <td class="border border-black p-1 text-center">
                                    <div>{{ \Carbon\Carbon::parse($req->date)->format('M d, Y') }}</div>
                                </td>
                                <td class="border border-black p-1">
                                    <div class="font-semibold truncate">{{ $req->vehicle ?? 'Assigned Unit' }}</div>
                                </td>
                                <td class="border border-black p-1 text-center font-bold">
                                    @if(in_array($req->status, ['completed', 'approved', 'on_trip']))
                                        <span class="text-emerald-900">{{ strtoupper($req->status) }}</span>
                                    @else
                                        <span class="text-gray-700">{{ strtoupper($req->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="border border-black p-2 text-center text-gray-500 italic">No trip records found for the selected scope.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <p class="text-[7.5px] text-gray-500 italic mt-0.5 text-right no-print">
                    *Ito ang condensed executive view para kasya sa 1 bond paper. Pindutin ang "Full Detailed Log" sa itaas kung nais ilista ang lahat ng {{ $requests->count() }} na biyahe.
                </p>
            </div>

            <!-- MODE 2: Full Detailed Trip Log (Hidden by default, toggled via action bar) -->
            <div id="fullTripLogSection" class="hidden mb-3">
                <h4 class="text-[10px] font-bold uppercase tracking-wider mb-1 text-black border-b border-black pb-0.5 flex justify-between items-center">
                    <span>3. Complete Official Dispatch Logbook Sheet</span>
                    <span class="text-[8.5px] font-mono text-gray-600">All {{ $requests->count() }} Records</span>
                </h4>
                <table class="w-full border-collapse border border-black text-[8px]">
                    <thead>
                        <tr class="bg-gray-100 font-bold">
                            <th class="border border-black p-1 text-center w-14">Req #</th>
                            <th class="border border-black p-1 text-left">Requester (Unit)</th>
                            <th class="border border-black p-1 text-left">Destination & Purpose</th>
                            <th class="border border-black p-1 text-center w-16">Date</th>
                            <th class="border border-black p-1 text-left w-24">Vehicle & Driver</th>
                            <th class="border border-black p-1 text-center w-14">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                            <tr>
                                <td class="border border-black p-0.5 text-center font-mono font-bold">{{ $req->request_number }}</td>
                                <td class="border border-black p-0.5">
                                    <div class="font-bold text-black">{{ $req->employee_name }}</div>
                                    <div class="text-[7px] text-gray-600">{{ $req->department }}</div>
                                </td>
                                <td class="border border-black p-0.5">
                                    <div class="font-semibold">{{ $req->destination }}</div>
                                    <div class="text-[7px] text-gray-600">{{ $req->purpose }}</div>
                                </td>
                                <td class="border border-black p-0.5 text-center">
                                    <div>{{ \Carbon\Carbon::parse($req->date)->format('M d, Y') }}</div>
                                </td>
                                <td class="border border-black p-0.5">
                                    <div>{{ $req->vehicle ?? 'N/A' }}</div>
                                    <div class="text-[7px] text-gray-600 font-semibold">{{ $req->tripTicket?->driver?->name ?? 'No Driver' }}</div>
                                </td>
                                <td class="border border-black p-0.5 text-center font-bold">
                                    {{ strtoupper($req->status) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="border border-black p-2 text-center text-gray-500 italic">No trip records found for the selected filter parameters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Section 4: Administrative Signatures (3 Official Signatories) -->
            <div class="page-break-avoid mt-3 pt-2.5 border-t border-black text-[10px]">
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div class="flex flex-col justify-end">
                        <span class="font-bold text-black uppercase">{{ \App\Models\CampusSetting::get('gso_name', 'JOEL A. TUMAMAO') }}</span>
                        <span class="text-[8px] uppercase text-gray-700 font-semibold">{{ \App\Models\CampusSetting::get('gso_title', 'General Services Officer') }}</span>
                        <div class="border-t border-black pt-0.5 mt-0.5 font-bold text-[8px] uppercase">Prepared by:</div>
                        <span class="text-[7.5px] font-mono text-gray-600 mt-0.5">✓ Digital System Certified</span>
                    </div>

                    <div class="flex flex-col justify-end">
                        <span class="font-bold text-black uppercase">{{ \App\Models\CampusSetting::get('cao_name', 'DR. ARLENE D. TALAMAYAN') }}</span>
                        <span class="text-[8px] uppercase text-gray-700 font-semibold">{{ \App\Models\CampusSetting::get('cao_title', 'Chief Administrative Officer') }}</span>
                        <div class="border-t border-black pt-0.5 mt-0.5 font-bold text-[8px] uppercase">Verified by:</div>
                        <span class="text-[7.5px] font-mono text-gray-600 mt-0.5">✓ Administrative Verification</span>
                    </div>

                    <div class="flex flex-col justify-end">
                        <span class="font-bold text-black uppercase">{{ \App\Models\CampusSetting::get('ceo_name', 'ENGR. JAMES B. CABILDO, PHD, ASEAN ENGR.') }}</span>
                        <span class="text-[8px] uppercase text-gray-700 font-semibold">{{ \App\Models\CampusSetting::get('ceo_title', 'Campus Executive Officer') }}</span>
                        <div class="border-t border-black pt-0.5 mt-0.5 font-bold text-[8px] uppercase">Approved by:</div>
                        <span class="text-[7.5px] font-mono font-bold text-emerald-800 bg-emerald-50 border border-emerald-300 px-1 py-0.5 rounded mt-0.5 inline-block">
                            ✓ Official Approved Report
                        </span>
                    </div>
                </div>
            </div>

            <!-- Document Footer Code -->
            <div class="mt-3 pt-1 border-t border-gray-300 flex justify-between text-[8px] text-gray-500 font-mono">
                <span>CSU Lal-lo Campus • General Services Fleet Management System (PeliCle)</span>
                <span>Document Control No. CSU-GSO-ANL-{{ date('Y') }}</span>
            </div>

        </div>
    </div>

    <!-- PDF Download and Mobile Script -->
    <script>
        let currentMode = 'executive'; // Default: 1-Page Executive Summary

        function setReportMode(mode) {
            currentMode = mode;
            const execBtn = document.getElementById('modeExecutiveBtn');
            const fullBtn = document.getElementById('modeFullBtn');
            const execView = document.getElementById('executiveTripSummarySection');
            const fullView = document.getElementById('fullTripLogSection');
            const modeBadge = document.getElementById('reportModeBadge');

            if (mode === 'executive') {
                execBtn.className = 'px-2.5 py-1.5 rounded-md bg-white text-gray-900 shadow-sm border border-gray-200 font-bold flex items-center gap-1 transition-all cursor-pointer';
                fullBtn.className = 'px-2.5 py-1.5 rounded-md text-gray-600 hover:text-gray-900 font-medium flex items-center gap-1 transition-all cursor-pointer';
                execView.classList.remove('hidden');
                fullView.classList.add('hidden');
                if (modeBadge) modeBadge.textContent = '1-Page Executive Summary';
            } else {
                fullBtn.className = 'px-2.5 py-1.5 rounded-md bg-white text-gray-900 shadow-sm border border-gray-200 font-bold flex items-center gap-1 transition-all cursor-pointer';
                execBtn.className = 'px-2.5 py-1.5 rounded-md text-gray-600 hover:text-gray-900 font-medium flex items-center gap-1 transition-all cursor-pointer';
                execView.classList.add('hidden');
                fullView.classList.remove('hidden');
                if (modeBadge) modeBadge.textContent = 'Comprehensive Audit Log (All Records)';
            }
        }

        function handleBack(event) {
            if (window.history.length > 1 && document.referrer && document.referrer.indexOf(window.location.host) !== -1) {
                event.preventDefault();
                window.history.back();
                return;
            }
            if (window.opener) {
                event.preventDefault();
                window.close();
                return;
            }
            try {
                window.close();
            } catch (e) {}
        }

        function isInAppBrowser() {
            return /FBAN|FBAV|Instagram|Line|Twitter|Snapchat|MicroMessenger/i.test(navigator.userAgent);
        }

        if (isInAppBrowser()) {
            const notice = document.getElementById('inAppNotice');
            if (notice) notice.classList.remove('hidden');
        }

        function triggerPrint() {
            if (isInAppBrowser()) {
                if (confirm('Hindi sinusuportahan ang direct printing sa loob ng Messenger.\n\nGusto mo bang i-download na lamang ito bilang PDF?')) {
                    downloadPDF();
                }
                return;
            }

            try {
                window.print();
            } catch (e) {
                console.warn('window.print error:', e);
                downloadPDF();
            }
        }

        let isGeneratingPdf = false;
        function downloadPDF() {
            if (isGeneratingPdf) return;
            isGeneratingPdf = true;

            const btn = document.getElementById('downloadPdfBtn');
            const btnText = document.getElementById('downloadBtnText');
            const originalText = btnText ? btnText.textContent : 'Download PDF';

            if (btn) btn.disabled = true;
            if (btnText) btnText.innerHTML = '<span class="inline-block animate-spin mr-1">⏳</span> Generating...';

            const element = document.querySelector('.print-container');
            const isMobile = /Android|iPhone|iPad|iPod|webOS|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

            const filenamePrefix = currentMode === 'executive' 
                ? 'Fleet-Analytics-Executive-Summary-' 
                : 'Fleet-Analytics-Complete-Logbook-';

            const opt = {
                margin:       currentMode === 'executive' ? [0.25, 0.25, 0.25, 0.25] : [0.3, 0.3, 0.3, 0.3],
                filename:     filenamePrefix + '{{ date('Y-m-d') }}.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { 
                    scale: isMobile ? 1.4 : 2.0, 
                    useCORS: true, 
                    allowTaint: true,
                    logging: false,
                    scrollX: 0,
                    scrollY: 0,
                    windowWidth: 816
                },
                jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait', compress: true }
            };

            html2pdf().set(opt).from(element).toPdf().get('pdf').then(function(pdf) {
                const blob = pdf.output('blob');
                const blobUrl = URL.createObjectURL(blob);
                const fileName = opt.filename;

                const a = document.createElement('a');
                a.href = blobUrl;
                a.download = fileName;
                document.body.appendChild(a);
                a.click();
                setTimeout(() => {
                    if (document.body.contains(a)) document.body.removeChild(a);
                }, 1000);

                if (isMobile) {
                    showMobilePdfModal(blobUrl, fileName);
                }

                if (btnText) btnText.textContent = '✅ Downloaded!';
                setTimeout(() => {
                    if (btnText) btnText.textContent = originalText;
                    if (btn) btn.disabled = false;
                    isGeneratingPdf = false;
                }, 2000);
            }).catch(function(err) {
                console.error('PDF generation error:', err);
                if (confirm('Nagkaroon ng problema sa automated PDF generation sa browser na ito.\n\nGusto mo bang gamitin ang Print menu para i-save bilang PDF?')) {
                    triggerPrint();
                }
                if (btnText) btnText.textContent = originalText;
                if (btn) btn.disabled = false;
                isGeneratingPdf = false;
            });
        }

        function showMobilePdfModal(blobUrl, fileName) {
            let modal = document.getElementById('mobilePdfSuccessModal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'mobilePdfSuccessModal';
                modal.className = 'no-print fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-sm p-4';
                modal.innerHTML = `
                    <div class="bg-white rounded-2xl w-full max-w-sm p-5 shadow-2xl border border-gray-200 flex flex-col gap-3.5">
                        <div class="flex items-center justify-between border-b pb-2.5">
                            <div class="flex items-center gap-2">
                                <span class="text-xl">📄</span>
                                <h3 class="font-extrabold text-sm text-gray-900">PDF Document Ready!</h3>
                            </div>
                            <button type="button" onclick="closeMobilePdfModal()" class="text-gray-400 hover:text-gray-600 font-bold p-1 cursor-pointer">✕</button>
                        </div>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            Nagawa na ang iyong PDF form! Pindutin ang button sa ibaba upang i-save o i-print sa iyong phone:
                        </p>
                        <div class="flex flex-col gap-2 pt-1">
                            <a id="mobileDownloadPdfLink" href="${blobUrl}" download="${fileName}" class="w-full bg-green-600 hover:bg-green-700 active:bg-green-800 text-white font-bold py-3 px-4 rounded-xl text-center text-xs flex items-center justify-center gap-2 shadow-sm">
                                <span>⬇️</span> <span>I-download ang PDF File</span>
                            </a>
                            <button type="button" onclick="closeMobilePdfModal(); triggerPrint();" class="w-full bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white font-bold py-2.5 px-4 rounded-xl text-center text-xs flex items-center justify-center gap-2 shadow-sm cursor-pointer">
                                <span>🖨️</span> <span>I-print / Save as PDF (Native)</span>
                            </button>
                            <button type="button" onclick="closeMobilePdfModal()" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 px-4 rounded-xl text-xs text-center cursor-pointer">
                                Isara (Close)
                            </button>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
            } else {
                const link = document.getElementById('mobileDownloadPdfLink');
                if (link) {
                    link.href = blobUrl;
                    link.download = fileName;
                }
                modal.classList.remove('hidden');
                modal.style.display = 'flex';
            }
        }

        function closeMobilePdfModal() {
            const modal = document.getElementById('mobilePdfSuccessModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
