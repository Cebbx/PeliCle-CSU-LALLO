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
        }
        @media print {
            body {
                background: white;
                color: black;
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
            }
            @page {
                size: letter; /* Force US Letter paper size */
                margin: 0.4in;
            }
        }
    </style>
</head>
@php
    $backUrl = url()->previous();
    if ($backUrl === url()->current() || empty($backUrl)) {
        if (auth('driver')->check()) {
            $backUrl = url('/driver/trip-tickets');
        } elseif (auth('employee')->check()) {
            $backUrl = url('/employee');
        } else {
            $backUrl = url('/admin/trip-tickets');
        }
    }
@endphp
<body class="bg-gray-100 py-4 sm:py-6 px-2 sm:px-4">

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

    <!-- Floating Top Bar (hidden on print) -->
    <div class="no-print max-w-[8.5in] mx-auto mb-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between bg-white p-3 sm:p-4 rounded-xl shadow-md border border-gray-200 gap-3">
        <div class="flex items-center justify-between sm:justify-start gap-3">
            <a href="{{ $backUrl }}" onclick="handleBack(event)" class="bg-gray-100 hover:bg-gray-200 active:bg-gray-300 text-gray-700 font-bold py-2 px-3 sm:px-3.5 rounded-lg transition-colors flex items-center gap-1.5 text-xs sm:text-sm border border-gray-300 shadow-sm cursor-pointer shrink-0" title="Go Back">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                <span>Back</span>
            </a>
            <span class="text-gray-700 font-bold text-xs sm:text-sm truncate">Vehicle Trip Ticket</span>
        </div>
        <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
            @php
                $currentUser = auth('admin')->user() ?? auth('web')->user() ?? auth()->user();
                $isAdmin = $currentUser && strtolower($currentUser->role ?? '') === 'admin';
            @endphp
            @if($isAdmin)
                <a href="{{ route('trip-tickets.print-travel-order', [$ticket->id, 'type' => 'driver']) }}" target="_blank" class="flex-1 sm:flex-initial bg-amber-600 hover:bg-amber-700 text-white font-bold py-2.5 px-3 rounded-lg transition-colors flex items-center justify-center gap-1.5 text-xs sm:text-sm shadow-sm cursor-pointer">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Driver Travel Order</span>
                </a>
            @endif
            <button id="downloadPdfBtn" type="button" onclick="downloadPDF()" class="flex-1 sm:flex-initial bg-green-600 hover:bg-green-700 active:bg-green-800 text-white font-bold py-2.5 px-3.5 rounded-lg transition-colors flex items-center justify-center gap-2 text-xs sm:text-sm shadow-sm cursor-pointer disabled:opacity-50">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span id="downloadBtnText">Download PDF</span>
            </button>
            <button id="printDocBtn" type="button" onclick="triggerPrint()" class="flex-1 sm:flex-initial bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white font-bold py-2.5 px-3.5 rounded-lg transition-colors flex items-center justify-center gap-2 text-xs sm:text-sm shadow-sm cursor-pointer">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Print Document</span>
            </button>
        </div>
    </div>

    <!-- Wrapper for smooth mobile horizontal scroll if needed -->
    <div class="overflow-x-auto w-full max-w-[8.5in] mx-auto pb-4">
        <!-- Main Ticket Sheet -->
        <div class="bg-white w-full max-w-[8.5in] mx-auto p-4 sm:p-8 flex flex-col print-container" style="min-height: 10.5in; min-width: 720px;">
        <!-- Header -->
        <div class="w-full border-b-2 border-black pb-4 mb-6 flex justify-center">
            <div class="relative flex flex-col items-center">
                <img src="/csu-logo.png" alt="CSU Logo" class="absolute right-[100%] mr-6 top-1/2 -translate-y-1/2 w-16 h-16 object-contain" />
                <h2 class="text-xs uppercase tracking-wider text-black">Republic of the Philippines</h2>
                <h1 class="text-sm font-extrabold uppercase text-black tracking-wide mt-0.5">Cagayan State University</h1>
                <h3 class="text-xs text-black mt-0.5">Lal-lo, Cagayan</h3>
            </div>
        </div>

        <!-- Title and QR Code row -->
        @php
            $allRequests = $ticket->all_vehicle_requests;
            $isCarpool = count($allRequests) > 1;
            $hasAnyUrgent = $allRequests->contains('is_urgent', true);
        @endphp
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-sm font-extrabold tracking-wide uppercase border-b border-black inline-block pb-0.5">Vehicle Trip Ticket</h1>
                <div class="text-xs font-bold text-black mt-2 font-mono">{{ $ticket->formatted_ticket_number }}</div>
                @if($hasAnyUrgent)
                    <div class="mt-1 flex flex-wrap items-center gap-1.5">
                        <span class="inline-block px-2 py-0.5 border border-red-600 text-red-600 font-extrabold text-[9px] tracking-widest uppercase rounded">🚨 URGENT DISPATCH</span>
                    </div>
                @endif
            </div>
            
            <!-- Scan to Complete QR Code -->
            <div class="flex flex-col items-center border border-black p-1 bg-white rounded shadow-sm">
                @php
                    $completionUrl = route('trip-tickets.complete-via-qr', ['ticket_number' => $ticket->ticket_number]);
                    $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=85x85&data=' . urlencode($completionUrl);
                @endphp
                <img src="{{ $qrCodeUrl }}" alt="Completion QR" class="w-[85px] h-[85px] object-contain" />
                <span class="text-[8px] font-bold text-black mt-0.5 uppercase tracking-wider">Scan to Complete</span>
            </div>
        </div>

        <!-- Details Table Grid -->
        <table class="w-full border-collapse border border-black mb-6 text-xs">
            <tbody>
                <tr>
                    <td class="border border-black p-2.5 font-bold bg-gray-50 w-28">Date:</td>
                    <td class="border border-black p-2.5 w-1/2">{{ \Carbon\Carbon::parse($ticket->vehicleRequest?->date ?? $ticket->created_at)->format('F d, Y') }}</td>
                    <td class="border border-black p-2.5 font-bold bg-gray-50 w-24">Vehicle:</td>
                    <td class="border border-black p-2.5">{{ $vehicleModel }}</td>
                </tr>
                <tr>
                    <td class="border border-black p-2.5 font-bold bg-gray-50">Driver's Name:</td>
                    <td class="border border-black p-2.5 font-bold">{{ $ticket->driver?->name ?? 'N/A' }}</td>
                    <td class="border border-black p-2.5 font-bold bg-gray-50">PLATE No:</td>
                    <td class="border border-black p-2.5 font-bold">{{ $vehiclePlate }}</td>
                </tr>
                <tr>
                    <td class="border border-black p-2.5 font-bold bg-gray-50">Authorized Passenger/s:</td>
                    <td class="border border-black p-2.5" colspan="3">
                        @if($isCarpool)
                            <div class="space-y-2 py-0.5">
                                @foreach($allRequests as $req)
                                    @php
                                        $passengers = $req->passenger_names ?? [];
                                        if (is_string($passengers)) {
                                            $passengers = json_decode($passengers, true) ?? [];
                                        }
                                        $pNames = collect($passengers)->pluck('name')->join(', ');
                                    @endphp
                                    <div class="border-b border-gray-200 pb-1.5 last:border-b-0 last:pb-0">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-black uppercase text-[10px] bg-gray-100 px-1.5 py-0.5 rounded border border-gray-300">
                                                {{ $req->department ?: 'N/A' }} ({{ $req->number_of_passengers }} {{ ($req->number_of_passengers ?: 1) > 1 ? 'persons' : 'person' }})
                                            </span>
                                            <span class="font-semibold text-black text-xs">
                                                {{ $pNames ?: $req->employee_name }}
                                            </span>
                                        </div>
                                        @if($req->has_other_passengers && $req->other_passengers)
                                            <div class="text-[9px] text-gray-700 italic ml-2 mt-0.5">
                                                <span class="font-bold uppercase text-[8px] text-gray-500">Others/Students:</span> {{ $req->other_passengers }}
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
                                <div class="mt-1 text-xs font-semibold text-black">
                                    <span class="font-bold uppercase text-[9px] text-gray-600">Others/Students:</span> {{ $ticket->vehicleRequest->other_passengers }}
                                </div>
                            @endif
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="border border-black p-2.5 font-bold bg-gray-50">Place to Visit:</td>
                    <td class="border border-black p-2.5" colspan="3">
                        @php
                            $destinations = $allRequests->pluck('destination')->unique()->values();
                        @endphp
                        @if(count($destinations) > 1)
                            <div class="space-y-1">
                                @foreach($allRequests as $req)
                                    <div><span class="font-bold text-[10px] text-gray-700">[{{ $req->department }}]:</span> {{ $req->destination }}</div>
                                @endforeach
                            </div>
                        @else
                            {{ $destinations->first() ?? $ticket->vehicleRequest?->destination ?? 'N/A' }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="border border-black p-2.5 font-bold bg-gray-50">Purpose/s:</td>
                    <td class="border border-black p-2.5" colspan="3">
                        @if($isCarpool)
                            <div class="space-y-1">
                                @foreach($allRequests as $req)
                                    <div>
                                        <span class="font-bold text-[10px] text-gray-800">[{{ $req->department }}]:</span>
                                        <span class="text-black font-medium">{{ $req->purpose }}</span>
                                        @if($req->is_urgent)
                                            <span class="ml-1 px-1 py-0.2 text-[8px] font-bold text-red-600 border border-red-500 rounded uppercase">Urgent</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            {{ $ticket->vehicleRequest?->purpose ?? 'N/A' }}
                            @if($ticket->vehicleRequest?->is_urgent)
                                <span class="ml-2 px-1.5 py-0.5 text-[9px] font-bold text-red-600 border border-red-500 rounded uppercase">Priority: Urgent</span>
                            @endif
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Certifications & Prepared/Approved signatures -->
        <div class="mb-6 text-xs">
            <p class="italic font-semibold mb-4">I CERTIFY that the vehicle is in GOOD RUNNING CONDITION:</p>
            
            <div class="grid grid-cols-2 gap-8 mt-2">
                <!-- Left: Prepared by Joel A. Tumamao -->
                <div class="flex flex-col text-left">
                    <span class="text-xs text-black font-semibold mb-6">Prepared by:</span>
                    <span class="text-xs font-extrabold text-black uppercase">JOEL A. TUMAMAO</span>
                    <span class="text-[10px] text-black font-semibold">General Services Officer</span>
                </div>
                
                <!-- Right: Approved by Engr. James B. Cabildo -->
                <div class="flex flex-col text-center">
                    <span class="text-xs text-black font-semibold mb-6 text-left pl-4">Approved by:</span>
                    <span class="text-xs font-extrabold text-black uppercase">ENGR. JAMES B. CABILDO, PHD, ASEAN ENGR.</span>
                    <span class="text-[10px] text-black font-semibold">Campus Executive Officer</span>
                </div>
            </div>
        </div>


        <!-- Itinerary to be filled by driver -->
        <div class="border border-black p-3 mb-3">
            <span class="text-[10px] font-extrabold uppercase tracking-wide text-black block mb-1">To be filled up by Driver:</span>
            <span class="text-xs underline font-bold text-black block mb-1">Itinerary of Travel:</span>
            
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

            <table class="w-full border-collapse border border-black text-[10px]">
                <thead>
                    <tr>
                        <th class="border border-black p-1 text-center font-bold" rowspan="2">DATE</th>
                        <th class="border border-black p-1 text-center font-bold" colspan="2">DEPARTURE</th>
                        <th class="border border-black p-1 text-center font-bold" colspan="2">ARRIVAL</th>
                    </tr>
                    <tr>
                        <th class="border border-black p-1 text-center font-bold">TIME</th>
                        <th class="border border-black p-1 text-center font-bold">PLACE</th>
                        <th class="border border-black p-1 text-center font-bold">TIME</th>
                        <th class="border border-black p-1 text-center font-bold">PLACE</th>
                    </tr>
                </thead>
                <tbody>
                    @if($hasDigitalLogs)
                        <tr>
                            <td class="border border-black p-2 font-mono font-semibold text-center">{{ $travelDate }}</td>
                            <td class="border border-black p-2 font-mono font-semibold text-center">{{ $depTime }}</td>
                            <td class="border border-black p-2 font-semibold">CSU Lal-lo Campus</td>
                            <td class="border border-black p-2 font-mono font-semibold text-center">{{ $arrTime ?: '---' }}</td>
                            <td class="border border-black p-2 font-semibold">{{ $dest }}</td>
                        </tr>
                        @for($i=0; $i<3; $i++)
                            <tr>
                                <td class="border border-black p-2 h-6"></td>
                                <td class="border border-black p-2"></td>
                                <td class="border border-black p-2"></td>
                                <td class="border border-black p-2"></td>
                                <td class="border border-black p-2"></td>
                            </tr>
                        @endfor
                    @else
                        @for($i=0; $i<4; $i++)
                            <tr>
                                <td class="border border-black p-2 h-6"></td>
                                <td class="border border-black p-2"></td>
                                <td class="border border-black p-2"></td>
                                <td class="border border-black p-2"></td>
                                <td class="border border-black p-2"></td>
                            </tr>
                        @endfor
                    @endif
                </tbody>
            </table>

            @if($ticket->start_odometer || $ticket->end_odometer)
                <div class="mt-2 p-1.5 bg-gray-50 border border-black text-[9px] flex justify-between items-center font-mono">
                    <div>
                        <span class="font-bold">Starting Odometer:</span> {{ $ticket->start_odometer ? number_format($ticket->start_odometer) . ' km' : 'N/A' }}
                    </div>
                    <div>
                        <span class="font-bold">Arrival Odometer:</span> {{ $ticket->end_odometer ? number_format($ticket->end_odometer) . ' km' : 'N/A' }}
                    </div>
                    <div>
                        <span class="font-bold">Total Distance Traveled:</span> 
                        <span class="font-bold text-black underline">{{ $ticket->distance_traveled ? number_format($ticket->distance_traveled) . ' km' : 'N/A' }}</span>
                    </div>
                </div>
            @endif
        </div>

        <!-- Post-travel Certification -->
        <div class="mb-3 text-xs mb-auto">
            <p class="font-semibold mb-2">I CERTIFY the Condition of Vehicle after travel:</p>
            <div class="flex justify-between items-end">
                <div class="flex flex-col gap-1.5">
                    <label class="flex items-center gap-2 cursor-default pointer-events-none">
                        <input type="checkbox" disabled {{ $ticket->status === 'completed' ? 'checked' : '' }} class="w-4 h-4 accent-black pointer-events-none cursor-default" />
                        <span class="font-bold text-black">GOOD</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-default pointer-events-none">
                        <input type="checkbox" disabled class="w-4 h-4 accent-black pointer-events-none cursor-default" />
                        <span class="font-bold text-black">Not GOOD (for Service maintenance)</span>
                    </label>
                </div>
                
                <div class="flex flex-col items-center w-64">
                    <div class="h-6 flex items-center justify-center">
                        @if($ticket->status === 'completed')
                            <span class="text-[10px] font-bold text-black uppercase tracking-wider">{{ $ticket->driver?->name }}</span>
                        @endif
                    </div>
                    <div class="w-full border-t border-black text-center pt-1 text-[10px] font-bold text-black uppercase">Driver's Signature</div>
                </div>
            </div>
        </div>

        <!-- Document Metadata footer details -->
        <div class="pt-4 flex justify-between items-center text-[10px] text-black font-mono border-t border-black mt-4">
            <span>F - GSO - 61202</span>
            <span>Rev. No. 02, October 20, 2025</span>
        </div>
        </div>
    </div>

    <!-- PDF Download & Print Script -->
    <script>
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

        // Show banner if running inside In-App browser
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

            const opt = {
                margin:       0.3,
                filename:     'Vehicle-Trip-Ticket-{{ $ticket->ticket_number }}.pdf',
                image:        { type: 'jpeg', quality: 0.95 },
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

                // 1. Try standard a.download anchor
                const a = document.createElement('a');
                a.href = blobUrl;
                a.download = fileName;
                document.body.appendChild(a);
                a.click();
                setTimeout(() => {
                    if (document.body.contains(a)) document.body.removeChild(a);
                }, 1000);

                // 2. On mobile / WebViews where a.download is ignored or blocked:
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
                alert('Nagkaroon ng problema sa paggawa ng PDF gamit ang browser na ito.\n\nTip: I-tap ang 3 dots (...) sa itaas at buksan sa Google Chrome para makapag-download o print.');
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
                            Nagawa na ang iyong PDF! Kung hindi kusang nag-download sa iyong cellphone, pindutin ang button sa ibaba:
                        </p>
                        <div class="flex flex-col gap-2 pt-1">
                            <a id="mobileViewPdfLink" href="${blobUrl}" target="_blank" class="w-full bg-green-600 hover:bg-green-700 active:bg-green-800 text-white font-bold py-2.5 px-4 rounded-xl text-center text-xs flex items-center justify-center gap-2 shadow-sm">
                                <span>👁️</span> <span>Buksan / I-view ang PDF</span>
                            </a>
                            <button type="button" onclick="closeMobilePdfModal()" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 px-4 rounded-xl text-xs text-center cursor-pointer">
                                Isara (Close)
                            </button>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
            } else {
                const link = document.getElementById('mobileViewPdfLink');
                if (link) link.href = blobUrl;
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
