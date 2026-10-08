<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
@php
    $backUrl = url()->previous();
    if ($backUrl === url()->current() || empty($backUrl)) {
        $backUrl = url('/admin/trip-logbook');
    }
@endphp
<body class="bg-gray-100 py-4 sm:py-6 px-2 sm:px-4">

    <!-- In-App Browser (Messenger / IG) Notification Banner -->
    <div id="inAppNotice" class="no-print hidden max-w-[13in] mx-auto mb-3 bg-amber-50 border border-amber-300 rounded-xl p-3 sm:p-3.5 shadow-sm text-xs text-amber-900 flex items-start justify-between gap-3">
        <div class="flex items-start gap-2.5">
            <span class="text-base leading-none">📱</span>
            <div class="leading-relaxed">
                <strong class="font-bold">Nasa loob ka ng Messenger / In-App browser:</strong>
                Maaaring hindi gumana ang Print o Download dito dahil hinarang ito ng app. Pindutin ang <strong>3 dots (⋮ o •••)</strong> sa kanang itaas ng screen at piliin ang <strong>"Open in Chrome" / "Open in Browser"</strong>.
            </div>
        </div>
        <button type="button" onclick="document.getElementById('inAppNotice').classList.add('hidden')" class="text-amber-700 hover:text-amber-900 font-bold p-1 cursor-pointer">✕</button>
    </div>

    <!-- Top Action Bar -->
    <div class="no-print max-w-[13in] mx-auto mb-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between bg-white p-3 sm:p-4 rounded-xl shadow-md border border-gray-200 gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ $backUrl }}" onclick="handleBack(event)" class="bg-gray-100 hover:bg-gray-200 active:bg-gray-300 text-gray-700 font-bold py-2 px-3 sm:px-3.5 rounded-lg transition-colors flex items-center gap-1.5 text-xs border border-gray-300 shadow-sm cursor-pointer shrink-0" title="Go Back">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                <span>Back</span>
            </a>
            <div class="min-w-0">
                <h1 class="text-gray-800 font-bold text-sm sm:text-base truncate">Monthly Vehicle Travel Logbook &mdash; {{ $monthName }}</h1>
                <p class="text-[11px] text-gray-500 font-mono truncate">Official GSO Record &bull; Generated: {{ \Carbon\Carbon::now('Asia/Manila')->format('F d, Y h:i A') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <button id="downloadPdfBtn" type="button" onclick="downloadPDF()" class="flex-1 sm:flex-initial bg-green-600 hover:bg-green-700 active:bg-green-800 text-white font-bold py-2.5 px-3.5 rounded-lg transition-colors flex items-center justify-center gap-2 text-xs shadow-sm cursor-pointer disabled:opacity-50">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span id="downloadBtnText">Download PDF</span>
            </button>
            <button id="printDocBtn" type="button" onclick="triggerPrint()" class="flex-1 sm:flex-initial bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold py-2.5 px-3.5 rounded-lg transition-colors flex items-center justify-center gap-2 text-xs shadow-sm cursor-pointer">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Print Logbook</span>
            </button>
        </div>
    </div>

    <!-- Wrapper for smooth mobile horizontal scroll -->
    <div class="overflow-x-auto w-full max-w-[13in] mx-auto pb-4">
        <!-- Official Printable Sheet Container -->
        <div class="print-container max-w-[13in] mx-auto bg-white p-6 rounded-xl shadow-lg border border-black min-h-[8in] text-black" style="min-width: 1024px;">
        
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
    </div>

    <!-- PDF Download and Mobile Script -->
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
                filename:     'Monthly-Vehicle-Logbook-{{ $year }}-{{ $month }}.pdf',
                image:        { type: 'jpeg', quality: 0.95 },
                html2canvas:  { 
                    scale: isMobile ? 1.2 : 1.8, 
                    useCORS: true, 
                    allowTaint: true,
                    logging: false,
                    scrollX: 0,
                    scrollY: 0,
                    windowWidth: 1344
                },
                jsPDF:        { unit: 'in', format: 'legal', orientation: 'landscape', compress: true }
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
