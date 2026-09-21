<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CSU Lal-lo - Monthly Vehicle Travel Logbook ({{ $monthName }})</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        body {
            font-family: 'Arial', 'Calibri', sans-serif;
            background-color: #f3f4f6;
            color: black;
        }
        @media print {
            body {
                background: white;
                color: black;
                font-size: 11px;
            }
            .no-print {
                display: none !important;
            }
            @page {
                size: legal landscape;
                margin: 0.3in;
            }
            .print-container {
                padding: 0 !important;
                margin: 0 !important;
                min-height: auto !important;
                box-shadow: none !important;
                border: none !important;
                max-width: 100% !important;
                width: 100% !important;
            }
        }
    </style>
</head>
<body class="bg-gray-100 py-6 px-4">

    <!-- Top Action Bar -->
    <div class="no-print max-w-[13in] mx-auto mb-4 flex items-center justify-between bg-white p-4 rounded-xl shadow-md border border-gray-200 gap-4">
        <div>
            <h1 class="text-gray-800 font-bold text-base">Monthly Vehicle Travel Logbook &mdash; {{ $monthName }}</h1>
            <p class="text-xs text-gray-500 font-mono">Official GSO Record &bull; Generated: {{ \Carbon\Carbon::now('Asia/Manila')->format('F d, Y h:i A') }}</p>
        </div>
        <div class="flex gap-2">
            <button onclick="downloadPDF()" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition-colors flex items-center gap-2 text-xs shadow-sm cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Download PDF
            </button>
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors flex items-center gap-2 text-xs shadow-sm cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Print Logbook Sheet
            </button>
        </div>
    </div>

    <!-- Official Printable Sheet Container -->
    <div class="print-container max-w-[13in] mx-auto bg-white p-6 rounded-xl shadow-lg border border-black min-h-[8in] text-black">
        
        <!-- CSU Lal-lo Letterhead -->
        <div class="flex items-center justify-between border-b-2 border-black pb-3 mb-4">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/csu-logo.png') }}" alt="CSU Logo" class="w-14 h-14 object-contain" onerror="this.style.display='none'" />
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-widest text-black">Republic of the Philippines</div>
                    <div class="text-sm font-black uppercase text-black leading-tight">Cagayan State University</div>
                    <div class="text-xs font-semibold text-black">Lal-lo Campus &bull; Sta. Maria, Lal-lo, Cagayan</div>
                    <div class="text-[11px] font-bold text-blue-900 mt-0.5">GENERAL SERVICES OFFICE (GSO) &bull; MOTORPOOL & FLEET SECTION</div>
                </div>
            </div>
            <div class="text-right">
                <div class="text-sm font-black uppercase tracking-wide border-b border-black pb-0.5">MONTHLY VEHICLE TRAVEL LOGBOOK</div>
                <div class="text-xs font-mono font-bold mt-1 text-black">Period: {{ strtoupper($monthName) }}</div>
                <div class="text-[10px] font-semibold text-gray-600 mt-0.5">Total Trips Recorded: <span class="font-bold text-black">{{ $totalTrips }}</span></div>
            </div>
        </div>

        <!-- Monthly Summary Highlights -->
        <div class="grid grid-cols-4 gap-2 mb-3 text-center border border-black p-2 bg-gray-50 text-xs">
            <div>
                <span class="font-bold">Total Trips Dispatched:</span>
                <span class="font-black text-black ml-1">{{ $totalTrips }}</span>
            </div>
            <div>
                <span class="font-bold">Completed Trips:</span>
                <span class="font-black text-green-700 ml-1">{{ $completedTrips }}</span>
            </div>
            <div>
                <span class="font-bold">Active / On Trip:</span>
                <span class="font-black text-blue-700 ml-1">{{ $activeTrips }}</span>
            </div>
            <div>
                <span class="font-bold">Total Passengers Served:</span>
                <span class="font-black text-black ml-1">{{ $totalPassengers }} persons</span>
            </div>
        </div>

        <!-- Official Logbook Table -->
        <table class="w-full border-collapse border border-black text-[10px] mb-4">
            <thead>
                <tr class="bg-gray-100 font-bold text-center border-b border-black">
                    <th class="border border-black p-1.5 w-8">#</th>
                    <th class="border border-black p-1.5">TT CONTROL NO.</th>
                    <th class="border border-black p-1.5">DEPARTURE DATE</th>
                    <th class="border border-black p-1.5">TIME</th>
                    <th class="border border-black p-1.5">REQUESTING DEPT / PASSENGERS</th>
                    <th class="border border-black p-1.5">DESTINATION</th>
                    <th class="border border-black p-1.5">VEHICLE & PLATE</th>
                    <th class="border border-black p-1.5">DRIVER</th>
                    <th class="border border-black p-1.5">DEPARTURE / ARRIVAL LOG</th>
                    <th class="border border-black p-1.5 w-16">STATUS</th>
                </tr>
            </thead>
            <tbody>
                @forelse($trips as $index => $trip)
                    @php
                        $allReqs = $trip->all_vehicle_requests;
                        $primaryReq = $trip->vehicleRequest;
                        $depDate = $primaryReq?->date ? \Carbon\Carbon::parse($primaryReq->date)->format('Y-m-d') : ($trip->created_at ? $trip->created_at->format('Y-m-d') : '---');
                        $depTime = $primaryReq?->time ? \Carbon\Carbon::parse($primaryReq->time)->format('g:i A') : '---';
                        $tripPax = 0;
                        foreach ($allReqs as $r) { $tripPax += ($r->number_of_passengers ?: 1); }

                        // Check digital milestones
                        $depLog = \App\Models\ActivityLog::where('model_type', \App\Models\TripTicket::class)
                            ->where('model_id', $trip->id)
                            ->where('action', 'Departure Logged')
                            ->first();
                        $arrLog = \App\Models\ActivityLog::where('model_type', \App\Models\TripTicket::class)
                            ->where('model_id', $trip->id)
                            ->where('action', 'Arrival Logged')
                            ->first();
                    @endphp
                    <tr class="border-b border-black hover:bg-gray-50">
                        <td class="border border-black p-1 text-center font-bold">{{ $index + 1 }}</td>
                        <td class="border border-black p-1 font-mono font-bold whitespace-nowrap">{{ $trip->formatted_ticket_number }}</td>
                        <td class="border border-black p-1 font-mono text-center whitespace-nowrap">{{ $depDate }}</td>
                        <td class="border border-black p-1 font-mono text-center whitespace-nowrap">{{ $depTime }}</td>
                        <td class="border border-black p-1">
                            @if(count($allReqs) > 1)
                                <span class="font-bold">{{ $allReqs->pluck('department')->unique()->join(', ') }}</span>
                                <span class="block text-[9px] text-gray-600">Carpool: {{ $allReqs->pluck('employee_name')->join(', ') }} ({{ $tripPax }} persons)</span>
                            @else
                                <span class="font-bold">{{ $primaryReq?->department ?? 'General' }}</span>
                                <span class="block text-[9px] text-gray-600">{{ $primaryReq?->employee_name ?? 'N/A' }} ({{ $tripPax }} {{ $tripPax > 1 ? 'persons' : 'person' }})</span>
                            @endif
                        </td>
                        <td class="border border-black p-1">{{ $primaryReq?->destination ?? 'N/A' }}</td>
                        <td class="border border-black p-1 font-mono font-semibold whitespace-nowrap">{{ $trip->vehicle ?? 'N/A' }}</td>
                        <td class="border border-black p-1 whitespace-nowrap">{{ $trip->driver?->name ?? 'Unassigned' }}</td>
                        <td class="border border-black p-1 font-mono text-[9px]">
                            @if($depLog)
                                <span>Dep: {{ \Carbon\Carbon::parse($depLog->created_at)->format('g:i A') }}</span>
                            @endif
                            @if($arrLog)
                                <span class="block">Arr: {{ \Carbon\Carbon::parse($arrLog->created_at)->format('g:i A') }}</span>
                            @elseif(!$depLog && !$arrLog)
                                <span class="text-gray-400">---</span>
                            @endif
                        </td>
                        <td class="border border-black p-1 text-center font-bold uppercase text-[9px]">
                            {{ $trip->status === 'active' ? 'On Trip' : $trip->status }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="border border-black p-6 text-center text-gray-500 font-semibold">
                            No trip tickets recorded for {{ $monthName }}.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Signatures & Verification -->
        <div class="mt-8 grid grid-cols-2 gap-12 text-xs">
            <div>
                <div class="font-bold uppercase mb-8">Prepared by:</div>
                <div class="border-b border-black w-64 pb-0.5 text-center font-bold uppercase">
                    JOEL A. TUMAMAO
                </div>
                <div class="text-[10px] text-gray-700 uppercase">Head, General Services Office (GSO)</div>
            </div>

            <div class="text-right flex flex-col items-end">
                <div class="font-bold uppercase mb-8 self-end">Noted by:</div>
                <div class="border-b border-black w-64 pb-0.5 text-center font-bold uppercase self-end">
                    CAMPUS EXECUTIVE OFFICER
                </div>
                <div class="text-[10px] text-gray-700 uppercase self-end">CSU Lal-lo Campus</div>
            </div>
        </div>

        <!-- Footer Code -->
        <div class="mt-6 pt-2 border-t border-black flex justify-between items-center text-[9px] font-mono text-gray-600">
            <span>F-GSO-LOGBOOK-61202</span>
            <span>Cagayan State University Lal-lo Campus &bull; GSO Motorpool Section</span>
        </div>
    </div>

    <!-- PDF Download Script -->
    <script>
        function downloadPDF() {
            const element = document.querySelector('.print-container');
            const opt = {
                margin:       0.3,
                filename:     'Monthly-Vehicle-Logbook-{{ $year }}-{{ $month }}.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true },
                jsPDF:        { unit: 'in', format: 'legal', orientation: 'landscape' }
            };
            html2pdf().set(opt).from(element).save();
        }
    </script>
</body>
</html>
