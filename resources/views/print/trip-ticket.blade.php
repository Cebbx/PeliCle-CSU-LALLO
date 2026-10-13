<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Trip Ticket - {{ $ticket->ticket_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        body {
            font-family: 'Arial', 'Calibri', sans-serif;
            background-color: #f3f4f6; /* Gray background on screen */
            color: black;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        @media print {
            body {
                background: white !important;
                color: black !important;
                font-size: 11px;
                padding: 0 !important;
                margin: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .print-container {
                min-height: 0 !important;
                height: auto !important;
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
            }
            @page {
                size: letter; /* Force US Letter paper size */
                margin: 0.35in;
            }
        }
    </style>
</head>
<body class="bg-gray-100 py-3 sm:py-6 px-2 sm:px-4">

    <!-- Floating Top Bar (Sticky, Mobile Responsive, hidden on print) -->
    <div class="no-print sticky top-2 z-50 max-w-[8.5in] mx-auto mb-4 bg-white/95 backdrop-blur-sm p-3 sm:p-4 rounded-xl shadow-lg border border-gray-200 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="text-gray-800 font-extrabold text-xs sm:text-sm tracking-tight">Vehicle Trip Ticket</span>
            <span class="text-[10px] sm:text-xs text-gray-500 font-mono hidden sm:inline">({{ $ticket->ticket_number }})</span>
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
            <!-- Back Button -->
            <button onclick="if (window.history.length > 1) { window.history.back(); } else { window.close(); }" class="flex-1 sm:flex-initial bg-gray-100 hover:bg-gray-200 active:scale-95 text-gray-700 font-bold py-2 px-3 sm:px-3.5 rounded-lg transition-all flex items-center justify-center gap-1.5 text-xs sm:text-sm shadow-sm border border-gray-300 cursor-pointer select-none">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Back</span>
            </button>
            <button id="btnDownloadPdf" onclick="downloadPDF(this)" class="flex-1 sm:flex-initial bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold py-2 px-3 sm:px-3.5 rounded-lg transition-all flex items-center justify-center gap-1.5 text-xs sm:text-sm shadow-md cursor-pointer select-none">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Download PDF File</span>
            </button>
            <button id="btnPrintDoc" onclick="triggerPrint(this)" class="flex-1 sm:flex-initial bg-sky-600 hover:bg-sky-700 active:scale-95 text-white font-bold py-2 px-3 sm:px-3.5 rounded-lg transition-all flex items-center justify-center gap-1.5 text-xs sm:text-sm shadow-md cursor-pointer select-none">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Print Document</span>
            </button>
        </div>
    </div>

    <!-- Main Ticket Sheet -->
    <div class="bg-white w-full max-w-[8.5in] mx-auto p-3.5 sm:p-8 flex flex-col print-container shadow-md sm:shadow-lg border border-gray-200 box-border">
        <!-- Header (Properly Centered Header with Left Logo) -->
        <div class="relative w-full border-b-2 border-black pb-3 sm:pb-4 mb-4 sm:mb-6 flex items-center justify-center min-h-[56px] sm:min-h-[68px]">
            <img src="{{ asset('csu-logo.png') }}" alt="CSU Logo" crossorigin="anonymous" class="absolute left-1 sm:left-2 top-1/2 -translate-y-1/2 w-12 h-12 sm:w-16 sm:h-16 object-contain flex-shrink-0" onerror="this.src='/csu-logo-sm.png'" />
            <div class="text-center w-full px-14 sm:px-18">
                <h2 class="text-[10px] sm:text-xs uppercase tracking-wider text-black font-medium leading-tight">Republic of the Philippines</h2>
                <h1 class="text-xs sm:text-base font-extrabold uppercase text-black tracking-wide mt-0.5 leading-tight">Cagayan State University</h1>
                <h3 class="text-[10px] sm:text-xs text-black mt-0.5 font-medium leading-tight">Lal-lo, Cagayan</h3>
            </div>
        </div>

        <!-- Title and QR Code row -->
        @php
            $allRequests = $ticket->all_vehicle_requests;
            $isCarpool = count($allRequests) > 1;
            $hasAnyUrgent = $allRequests->contains('is_urgent', true);
        @endphp
        <div class="flex justify-between items-center mb-4 sm:mb-6 gap-2">
            <div>
                <h1 class="text-xs sm:text-sm font-extrabold tracking-wide uppercase border-b border-black inline-block pb-0.5">Vehicle Trip Ticket</h1>
                <div class="text-[11px] sm:text-xs font-bold text-black mt-1.5 font-mono">{{ $ticket->formatted_ticket_number }}</div>
                @if($hasAnyUrgent)
                    <div class="mt-1 flex flex-wrap items-center gap-1.5">
                        <span class="inline-block px-1.5 py-0.5 border border-red-600 text-red-600 font-extrabold text-[8px] sm:text-[9px] tracking-widest uppercase rounded">🚨 URGENT DISPATCH</span>
                    </div>
                @endif
            </div>
            
            <!-- Scan to Complete QR Code -->
            <div class="flex flex-col items-center border border-black p-1 bg-white rounded shadow-sm flex-shrink-0">
                @php
                    $completionUrl = route('trip-tickets.complete-via-qr', ['ticket_number' => $ticket->ticket_number]);
                    $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=' . urlencode($completionUrl);
                @endphp
                <img src="{{ $qrCodeUrl }}" alt="Completion QR" class="w-[65px] h-[65px] sm:w-[80px] sm:h-[80px] object-contain" />
                <span class="text-[7px] sm:text-[8px] font-bold text-black mt-0.5 uppercase tracking-wider">Scan to Complete</span>
            </div>
        </div>

        <!-- Details Table Grid -->
        <table class="w-full border-collapse border border-black mb-4 sm:mb-6 text-[10px] sm:text-xs">
            <tbody>
                <tr>
                    <td class="border border-black p-1.5 sm:p-2.5 font-bold bg-gray-50 w-24 sm:w-28">Date:</td>
                    <td class="border border-black p-1.5 sm:p-2.5 w-1/2">{{ \Carbon\Carbon::parse($ticket->vehicleRequest?->date ?? $ticket->created_at)->format('F d, Y') }}</td>
                    <td class="border border-black p-1.5 sm:p-2.5 font-bold bg-gray-50 w-20 sm:w-24">Vehicle:</td>
                    <td class="border border-black p-1.5 sm:p-2.5">{{ $vehicleModel }}</td>
                </tr>
                <tr>
                    <td class="border border-black p-1.5 sm:p-2.5 font-bold bg-gray-50">Driver's Name:</td>
                    <td class="border border-black p-1.5 sm:p-2.5 font-bold">{{ $ticket->driver?->name ?? 'N/A' }}</td>
                    <td class="border border-black p-1.5 sm:p-2.5 font-bold bg-gray-50">PLATE No:</td>
                    <td class="border border-black p-1.5 sm:p-2.5 font-bold">{{ $vehiclePlate }}</td>
                </tr>
                <tr>
                    <td class="border border-black p-1.5 sm:p-2.5 font-bold bg-gray-50">Authorized Passenger/s:</td>
                    <td class="border border-black p-1.5 sm:p-2.5" colspan="3">
                        @if($isCarpool)
                            <div class="space-y-1.5 py-0.5">
                                @foreach($allRequests as $req)
                                    @php
                                        $passengers = $req->passenger_names ?? [];
                                        if (is_string($passengers)) {
                                            $passengers = json_decode($passengers, true) ?? [];
                                        }
                                        $pNames = collect($passengers)->pluck('name')->join(', ');
                                    @endphp
                                    <div class="border-b border-gray-200 pb-1 last:border-b-0 last:pb-0">
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-bold text-black uppercase text-[9px] bg-gray-100 px-1 py-0.2 rounded border border-gray-300">
                                                {{ $req->department ?: 'N/A' }} ({{ $req->number_of_passengers }} {{ ($req->number_of_passengers ?: 1) > 1 ? 'persons' : 'person' }})
                                            </span>
                                            <span class="font-semibold text-black text-[10px] sm:text-xs">
                                                {{ $pNames ?: $req->employee_name }}
                                            </span>
                                        </div>
                                        @if($req->has_other_passengers && $req->other_passengers)
                                            <div class="text-[8px] sm:text-[9px] text-gray-700 italic ml-1.5 mt-0.5">
                                                <span class="font-bold uppercase text-[7px] sm:text-[8px] text-gray-500">Others/Students:</span> {{ $req->other_passengers }}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            @php
                                $passengers = $ticket->vehicleRequest?->passenger_names ?? [];
                                if (is_string($passengers)) {
                                    $passengers = json_decode($passengers, true) ?? [];
                                }
                                $passengerNames = collect($passengers)->pluck('name')->join(', ');
                            @endphp
                            <span class="font-semibold text-black">{{ $passengerNames ?: $ticket->vehicleRequest?->employee_name ?? 'N/A' }}</span>
                            @if($ticket->vehicleRequest?->has_other_passengers && $ticket->vehicleRequest?->other_passengers)
                                <div class="mt-1 text-[10px] sm:text-xs font-semibold text-black">
                                    <span class="font-bold uppercase text-[8px] sm:text-[9px] text-gray-600">Others/Students:</span> {{ $ticket->vehicleRequest->other_passengers }}
                                </div>
                            @endif
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="border border-black p-1.5 sm:p-2.5 font-bold bg-gray-50">Place to Visit:</td>
                    <td class="border border-black p-1.5 sm:p-2.5" colspan="3">
                        @php
                            $destinations = $allRequests->pluck('destination')->unique()->values();
                        @endphp
                        @if(count($destinations) > 1)
                            <div class="space-y-0.5">
                                @foreach($allRequests as $req)
                                    <div><span class="font-bold text-[9px] sm:text-[10px] text-gray-700">[{{ $req->department }}]:</span> {{ $req->destination }}</div>
                                @endforeach
                            </div>
                        @else
                            {{ $destinations->first() ?? $ticket->vehicleRequest?->destination ?? 'N/A' }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="border border-black p-1.5 sm:p-2.5 font-bold bg-gray-50">Purpose/s:</td>
                    <td class="border border-black p-1.5 sm:p-2.5" colspan="3">
                        @if($isCarpool)
                            <div class="space-y-0.5">
                                @foreach($allRequests as $req)
                                    <div>
                                        <span class="font-bold text-[9px] sm:text-[10px] text-gray-800">[{{ $req->department }}]:</span>
                                        <span class="text-black font-medium">{{ $req->purpose }}</span>
                                        @if($req->is_urgent)
                                            <span class="ml-1 px-1 py-0.2 text-[7px] sm:text-[8px] font-bold text-red-600 border border-red-500 rounded uppercase">Urgent</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            {{ $ticket->vehicleRequest?->purpose ?? 'N/A' }}
                            @if($ticket->vehicleRequest?->is_urgent)
                                <span class="ml-1.5 px-1 py-0.2 text-[8px] sm:text-[9px] font-bold text-red-600 border border-red-500 rounded uppercase">Priority: Urgent</span>
                            @endif
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Certifications & Prepared/Approved signatures -->
        <div class="mb-4 sm:mb-6 text-[10px] sm:text-xs">
            <p class="italic font-semibold mb-3">I CERTIFY that the vehicle is in GOOD RUNNING CONDITION:</p>
            
            <div class="grid grid-cols-2 gap-4 sm:gap-8 mt-1">
                <!-- Left: Prepared by Joel A. Tumamao -->
                <div class="flex flex-col text-left">
                    <span class="text-[10px] sm:text-xs text-black font-semibold mb-4 sm:mb-6">Prepared by:</span>
                    <span class="text-[10px] sm:text-xs font-extrabold text-black uppercase">JOEL A. TUMAMAO</span>
                    <span class="text-[8px] sm:text-[10px] text-black font-semibold">General Services Officer</span>
                </div>
                
                <!-- Right: Approved by Engr. James B. Cabildo -->
                <div class="flex flex-col text-center">
                    <span class="text-[10px] sm:text-xs text-black font-semibold mb-4 sm:mb-6 text-left pl-2 sm:pl-4">Approved by:</span>
                    <span class="text-[10px] sm:text-xs font-extrabold text-black uppercase">ENGR. JAMES B. CABILDO, PHD, ASEAN ENGR.</span>
                    <span class="text-[8px] sm:text-[10px] text-black font-semibold">Campus Executive Officer</span>
                </div>
            </div>
        </div>

        <!-- Itinerary to be filled by driver -->
        <div class="border border-black p-2 sm:p-3 mb-3">
            <span class="text-[9px] sm:text-[10px] font-extrabold uppercase tracking-wide text-black block mb-0.5">To be filled up by Driver:</span>
            <span class="text-[10px] sm:text-xs underline font-bold text-black block mb-1">Itinerary of Travel:</span>
            
            @php
                $depLog = \App\Models\ActivityLog::where('model_type', \App\Models\TripTicket::class)
                    ->where('model_id', $ticket->id)
                    ->where('action', 'Departure Logged')
                    ->first();
                $arrLog = \App\Models\ActivityLog::where('model_type', \App\Models\TripTicket::class)
                    ->where('model_id', $ticket->id)
                    ->where('action', 'Arrival Logged')
                    ->first();
                $hasDigitalLogs = ($depLog || $arrLog || $ticket->status === 'completed');
                $travelDate = $ticket->vehicleRequest?->date ? \Carbon\Carbon::parse($ticket->vehicleRequest->date)->format('Y-m-d') : '';
                $depTime = $depLog ? \Carbon\Carbon::parse($depLog->created_at)->format('g:i A') : ($ticket->vehicleRequest?->time ? \Carbon\Carbon::parse($ticket->vehicleRequest->time)->format('g:i A') : '');
                $arrTime = $arrLog ? \Carbon\Carbon::parse($arrLog->created_at)->format('g:i A') : ($ticket->status === 'completed' ? \Carbon\Carbon::parse($ticket->updated_at)->format('g:i A') : '');
                $dest = $ticket->vehicleRequest?->destination ?? '';
            @endphp

            <table class="w-full border-collapse border border-black text-[9px] sm:text-[10px]">
                <thead>
                    <tr>
                        <th class="border border-black p-0.5 sm:p-1 text-center font-bold" rowspan="2">DATE</th>
                        <th class="border border-black p-0.5 sm:p-1 text-center font-bold" colspan="2">DEPARTURE</th>
                        <th class="border border-black p-0.5 sm:p-1 text-center font-bold" colspan="2">ARRIVAL</th>
                    </tr>
                    <tr>
                        <th class="border border-black p-0.5 sm:p-1 text-center font-bold">TIME</th>
                        <th class="border border-black p-0.5 sm:p-1 text-center font-bold">PLACE</th>
                        <th class="border border-black p-0.5 sm:p-1 text-center font-bold">TIME</th>
                        <th class="border border-black p-0.5 sm:p-1 text-center font-bold">PLACE</th>
                    </tr>
                </thead>
                <tbody>
                    @if($hasDigitalLogs)
                        <tr>
                            <td class="border border-black p-1 sm:p-1.5 font-mono font-semibold text-center">{{ $travelDate }}</td>
                            <td class="border border-black p-1 sm:p-1.5 font-mono font-semibold text-center">{{ $depTime }}</td>
                            <td class="border border-black p-1 sm:p-1.5 font-semibold">CSU Lal-lo Campus</td>
                            <td class="border border-black p-1 sm:p-1.5 font-mono font-semibold text-center">{{ $arrTime ?: '---' }}</td>
                            <td class="border border-black p-1 sm:p-1.5 font-semibold">{{ $dest }}</td>
                        </tr>
                        @for($i=0; $i<2; $i++)
                            <tr>
                                <td class="border border-black p-1 sm:p-1.5 h-5 sm:h-6"></td>
                                <td class="border border-black p-1 sm:p-1.5"></td>
                                <td class="border border-black p-1 sm:p-1.5"></td>
                                <td class="border border-black p-1 sm:p-1.5"></td>
                                <td class="border border-black p-1 sm:p-1.5"></td>
                            </tr>
                        @endfor
                    @else
                        @for($i=0; $i<3; $i++)
                            <tr>
                                <td class="border border-black p-1 sm:p-1.5 h-5 sm:h-6"></td>
                                <td class="border border-black p-1 sm:p-1.5"></td>
                                <td class="border border-black p-1 sm:p-1.5"></td>
                                <td class="border border-black p-1 sm:p-1.5"></td>
                                <td class="border border-black p-1 sm:p-1.5"></td>
                            </tr>
                        @endfor
                    @endif
                </tbody>
            </table>

            @if($ticket->start_odometer || $ticket->end_odometer)
                <div class="mt-1.5 p-1 bg-gray-50 border border-black text-[8px] sm:text-[9px] flex justify-between items-center font-mono">
                    <div>
                        <span class="font-bold">Start:</span> {{ $ticket->start_odometer ? number_format($ticket->start_odometer) . ' km' : 'N/A' }}
                    </div>
                    <div>
                        <span class="font-bold">Arrival:</span> {{ $ticket->end_odometer ? number_format($ticket->end_odometer) . ' km' : 'N/A' }}
                    </div>
                    <div>
                        <span class="font-bold">Distance:</span> 
                        <span class="font-bold text-black underline">{{ $ticket->distance_traveled ? number_format($ticket->distance_traveled) . ' km' : 'N/A' }}</span>
                    </div>
                </div>
            @endif
        </div>

        <!-- Post-travel Certification -->
        <div class="mb-2 text-[10px] sm:text-xs">
            <p class="font-semibold mb-1.5">I CERTIFY the Condition of Vehicle after travel:</p>
            <div class="flex justify-between items-end gap-2">
                <div class="flex flex-col gap-1">
                    <label class="flex items-center gap-1.5 cursor-default pointer-events-none">
                        <input type="checkbox" disabled {{ $ticket->status === 'completed' ? 'checked' : '' }} class="w-3.5 h-3.5 accent-black pointer-events-none cursor-default" />
                        <span class="font-bold text-black">GOOD</span>
                    </label>
                    <label class="flex items-center gap-1.5 cursor-default pointer-events-none">
                        <input type="checkbox" disabled class="w-3.5 h-3.5 accent-black pointer-events-none cursor-default" />
                        <span class="font-bold text-black">Not GOOD (for Service maintenance)</span>
                    </label>
                </div>
                
                <div class="flex flex-col items-center w-48 sm:w-64">
                    <div class="h-5 sm:h-6 border-b border-gray-300 w-11/12 mx-auto mb-0.5 flex items-center justify-center">
                        @if($ticket->status === 'completed')
                            <span class="text-[9px] sm:text-[10px] font-bold text-black uppercase tracking-wider">{{ $ticket->driver?->name }}</span>
                        @endif
                    </div>
                    <div class="w-full border-t border-black text-center pt-0.5 text-[9px] sm:text-[10px] font-bold text-black uppercase">Driver's Signature</div>
                </div>
            </div>
        </div>

        <!-- Document Metadata footer details -->
        <div class="pt-2.5 sm:pt-4 flex justify-between items-center text-[9px] sm:text-[10px] text-black font-mono border-t border-black mt-2 sm:mt-4">
            <span>F - GSO - 61202</span>
            <span>Rev. No. 02, October 20, 2025</span>
        </div>
    </div>

    <!-- Mobile & PC Optimized 1-Page PDF Download & Print Scripts -->
    <script>
        async function downloadPDF(btn) {
            const btnEl = btn || document.querySelector('#btnDownloadPdf');
            const originalHtml = btnEl ? btnEl.innerHTML : '';

            if (typeof html2pdf === 'undefined') {
                window.print();
                return;
            }

            if (btnEl) {
                btnEl.disabled = true;
                btnEl.innerHTML = `
                    <svg class="animate-spin h-4 w-4 text-white inline-block flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Generating PDF...</span>
                `;
            }

            const element = document.querySelector('.print-container');
            const filename = 'Vehicle-Trip-Ticket-{{ $ticket->ticket_number }}.pdf';

            // Isolated offscreen container fixed at 816px for perfect full-width PDF rendering on all devices
            const offscreen = document.createElement('div');
            offscreen.style.position = 'fixed';
            offscreen.style.left = '-10000px';
            offscreen.style.top = '0';
            offscreen.style.width = '816px';
            offscreen.style.maxWidth = '816px';
            offscreen.style.minWidth = '816px';
            offscreen.style.backgroundColor = '#ffffff';
            offscreen.style.zIndex = '-9999';
            offscreen.style.overflow = 'visible';

            const clone = element.cloneNode(true);
            clone.style.width = '816px';
            clone.style.maxWidth = '816px';
            clone.style.minWidth = '816px';
            clone.style.margin = '0 auto';
            clone.style.padding = '24px 36px';
            clone.style.boxShadow = 'none';
            clone.style.border = 'none';
            clone.style.backgroundColor = '#ffffff';

            offscreen.appendChild(clone);
            document.body.appendChild(offscreen);

            const opt = {
                margin:       0,
                filename:     filename,
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  {
                    scale: 2,
                    useCORS: true,
                    allowTaint: true,
                    logging: false,
                    width: 816,
                    windowWidth: 816,
                    x: 0,
                    y: 0,
                    scrollX: 0,
                    scrollY: 0
                },
                jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' },
                pagebreak: { mode: ['avoid-all', 'css', 'legacy'] }
            };

            try {
                const pdfWorker = html2pdf().set(opt).from(clone);
                const blob = await pdfWorker.output('blob');
                const blobUrl = URL.createObjectURL(blob);

                const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;

                if (isIOS) {
                    const newWindow = window.open(blobUrl, '_blank');
                    if (!newWindow || newWindow.closed || typeof newWindow.closed === 'undefined') {
                        const link = document.createElement('a');
                        link.href = blobUrl;
                        link.download = filename;
                        document.body.appendChild(link);
                        link.click();
                        setTimeout(() => document.body.removeChild(link), 1500);
                    }
                } else {
                    const link = document.createElement('a');
                    link.href = blobUrl;
                    link.download = filename;
                    document.body.appendChild(link);
                    link.click();
                    setTimeout(() => {
                        document.body.removeChild(link);
                        URL.revokeObjectURL(blobUrl);
                    }, 3000);
                }

                if (btnEl) {
                    btnEl.innerHTML = `<span>✓ PDF Downloaded!</span>`;
                    setTimeout(() => {
                        btnEl.disabled = false;
                        btnEl.innerHTML = originalHtml;
                    }, 2500);
                }
            } catch (err) {
                console.error('PDF export error:', err);
                alert('Opening print preview where you can select "Save as PDF"!');
                window.print();
                if (btnEl) {
                    btnEl.disabled = false;
                    btnEl.innerHTML = originalHtml;
                }
            } finally {
                if (offscreen.parentNode) {
                    offscreen.parentNode.removeChild(offscreen);
                }
            }
        }

        function triggerPrint(btn) {
            if (btn) {
                btn.classList.add('opacity-75');
                setTimeout(() => btn.classList.remove('opacity-75'), 1200);
            }
            window.scrollTo(0, 0);
            window.print();
        }
    </script>

</body>
</html>
