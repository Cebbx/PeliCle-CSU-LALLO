<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Request Form - {{ $request->request_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        body {
            font-family: 'Arial', 'Calibri', sans-serif;
            background-color: #f3f4f6; /* Gray background on screen */
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
            .print-container {
                min-height: 0 !important;
                min-width: 0 !important;
                height: auto !important;
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
                overflow: visible !important;
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
        if (auth('employee')->check()) {
            $backUrl = url('/employee/vehicle-requests');
        } elseif (auth('driver')->check()) {
            $backUrl = url('/driver');
        } else {
            $backUrl = url('/admin/vehicle-requests');
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
            <span class="text-gray-700 font-bold text-xs sm:text-sm truncate">Vehicle Request Form</span>
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <button id="downloadPdfBtn" type="button" onclick="downloadPDF()" class="flex-1 sm:flex-initial bg-green-600 hover:bg-green-700 active:bg-green-800 text-white font-bold py-2.5 px-3.5 rounded-lg transition-colors flex items-center justify-center gap-2 text-xs sm:text-sm shadow-sm cursor-pointer disabled:opacity-50">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span id="downloadBtnText">Download PDF File</span>
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
        <!-- Main Form Page Sheet -->
        <div class="bg-white w-full max-w-[8.5in] mx-auto p-4 sm:p-8 shadow-lg border border-gray-200 flex flex-col print-container" style="min-height: 10.5in; min-width: 720px;">
        <!-- Header -->
        <div class="w-full border-b-2 border-black pb-4 mb-6 flex justify-center">
            <div class="relative flex flex-col items-center">
                <img src="/csu-logo.png" alt="CSU Logo" class="absolute right-[100%] mr-6 top-1/2 -translate-y-1/2 w-16 h-16 object-contain" />
                <h2 class="text-xs uppercase tracking-wider text-black">Republic of the Philippines</h2>
                <h1 class="text-sm font-extrabold uppercase text-black tracking-wide mt-0.5">Cagayan State University</h1>
                <h3 class="text-xs text-black mt-0.5">Lal-lo Campus</h3>
                <h2 class="text-[11px] font-bold uppercase tracking-wider text-black mt-1">General Service Office</h2>
            </div>
        </div>

        <!-- Title -->
        <div class="text-center mb-4">
            <h1 class="text-base font-extrabold tracking-wide uppercase border-b border-black inline-block pb-0.5">Vehicle Request Form</h1>
        </div>

        <!-- Request Details Table Grid -->
        <div class="border border-black mb-6">
            <!-- Row 1 -->
            <div class="grid grid-cols-12 border-b border-black">
                <div class="col-span-7 p-2.5 border-r border-black flex flex-col justify-between" style="min-height: 85px;">
                    <span class="text-[10px] uppercase font-bold text-black">Client:</span>
                    <div class="flex flex-col items-center justify-end w-full">
                        <span class="text-xs sm:text-sm font-bold text-black text-center truncate max-w-full px-1">{{ $request->employee_name }}</span>
                        <div class="border-b border-black w-11/12 mx-auto my-0.5"></div>
                        <span class="text-[9px] text-black text-center">Name</span>
                    </div>
                </div>
                <div class="col-span-5 flex flex-col justify-between" style="min-height: 85px;">
                    <!-- Top: Date Box (matches official format) -->
                    <div class="flex border-b border-black h-7">
                        <div class="w-16 border-r border-black px-2 flex items-center justify-center">
                            <span class="text-[10px] font-bold text-black">Date</span>
                        </div>
                        <div class="flex-1 px-3 flex items-center justify-center">
                            <span class="text-xs font-bold text-black">{{ \Carbon\Carbon::parse($request->created_at ?? now())->format('M d, Y') }}</span>
                        </div>
                    </div>
                    <!-- Bottom: Office Box (matches official format) -->
                    <div class="p-2 flex flex-col justify-end items-center flex-1">
                        <span class="text-xs sm:text-sm font-bold text-black text-center uppercase truncate max-w-full px-1">{{ $request->department }}</span>
                        <div class="border-b border-black w-11/12 mx-auto my-0.5"></div>
                        <span class="text-[9px] text-black text-center">Office</span>
                    </div>
                </div>
            </div>

            <!-- Row 2 -->
            <div class="grid grid-cols-12 border-b border-black">
                <div class="col-span-6 p-3 border-r border-black flex flex-col" style="min-height: 50px;">
                    <span class="text-[10px] uppercase font-bold text-black">Date of Travel:</span>
                    <span class="text-sm font-bold text-black mt-1">{{ \Carbon\Carbon::parse($request->date)->format('F d, Y') }}</span>
                </div>
                <div class="col-span-6 p-3 flex flex-col" style="min-height: 50px;">
                    <span class="text-[10px] uppercase font-bold text-black">Departure Time:</span>
                    <span class="text-sm font-bold text-black mt-1">{{ \Carbon\Carbon::parse($request->time)->format('g:i A') }}</span>
                </div>
            </div>

            <!-- Row 3 -->
            <div class="grid grid-cols-12 border-b border-black">
                <div class="col-span-12 p-3 flex flex-col" style="min-height: 50px;">
                    <span class="text-[10px] uppercase font-bold text-black">Destination:</span>
                    <span class="text-sm font-bold text-black mt-1">{{ $request->destination }}</span>
                </div>
            </div>

            <!-- Row 4 -->
            <div class="grid grid-cols-12 border-b border-black">
                <div class="col-span-12 p-3 flex flex-col" style="min-height: 70px;">
                    <span class="text-[10px] uppercase font-bold text-black">Purpose of Travel:</span>
                    <span class="text-sm font-medium text-black mt-1 leading-relaxed">{{ $request->purpose }}</span>
                </div>
            </div>

            <!-- Row 5 (Passengers) -->
            <div class="grid grid-cols-12">
                <div class="col-span-3 p-3 border-r border-black flex items-center justify-center">
                    <span class="text-[10px] uppercase font-bold text-black text-center">Name of Passengers</span>
                </div>
                <div class="col-span-9 p-3 flex flex-col gap-1.5">
                    @php
                        $passengers = $request->passenger_names ?? [];
                        if (is_string($passengers)) {
                            $passengers = json_decode($passengers, true) ?? [];
                        }
                    @endphp
                    @if(count($passengers) > 0)
                        <div class="grid grid-cols-2 gap-2 text-xs font-semibold text-black">
                            @foreach($passengers as $idx => $p)
                                <div class="border-b border-gray-300 pb-0.5">{{ $idx + 1 }}. {{ $p['name'] ?? $p }}</div>
                            @endforeach
                        </div>
                    @else
                        <span class="text-xs text-black font-light italic">No passengers specified.</span>
                    @endif

                    @if($request->has_other_passengers && $request->other_passengers)
                        <div class="mt-2 pt-1 border-t border-gray-300 text-xs text-black">
                            <span class="font-bold uppercase text-[10px] text-gray-700">Others / Students:</span>
                            <span class="font-semibold text-black ml-1">{{ $request->other_passengers }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Footer approvals section -->
        <div class="grid grid-cols-12 gap-8 mt-6 mb-auto">
            <!-- Left: Client's Signature and Received by -->
            <div class="col-span-6 flex flex-col justify-between pr-4">
                <!-- Client's Signature -->
                <div class="flex flex-col items-center">
                    <div class="h-14 relative flex items-end justify-center w-full">
                        @if(!empty($request->signature_url))
                            <img src="{{ $request->signature_url }}" alt="Client Signature" class="max-h-14 max-w-[200px] object-contain mb-1" />
                        @endif
                    </div>
                    <div class="w-full text-center pb-1">
                        <span class="text-sm font-extrabold text-black uppercase tracking-wide">{{ $request->employee_name }}</span>
                    </div>
                    <div class="w-full border-t border-black text-center pt-1">
                        <span class="text-[10px] text-black uppercase font-semibold">Client Signature</span>
                    </div>
                </div>

                <!-- Received by -->
                <div class="flex flex-col mt-8">
                    <span class="text-[10px] font-bold text-black uppercase">Received by:</span>
                    <div class="w-full border-b border-black mt-6"></div>
                </div>
            </div>

            <!-- Right: Approved / Disapproved checkboxes and Joel Tumamao signature -->
            <div class="col-span-6 flex flex-col justify-between pl-6">
                <!-- Checkboxes (Read-only / Solid Black) -->
                @php
                    $isApproved = in_array($request->status, ['approved', 'completed', 'on_trip']);
                    $isDisapproved = $request->status === 'rejected';
                @endphp
                <div class="flex items-center gap-6 text-xs font-bold text-black select-none pointer-events-none h-14">
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded flex items-center justify-center border border-black {{ $isApproved ? 'bg-black text-white' : 'bg-white' }}" style="border-width: 1.5px;">
                            @if($isApproved)
                                <svg class="w-3 h-3 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            @endif
                        </div>
                        <span>Approved</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded flex items-center justify-center border border-black {{ $isDisapproved ? 'bg-black text-white' : 'bg-white' }}" style="border-width: 1.5px;">
                            @if($isDisapproved)
                                <svg class="w-3 h-3 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            @endif
                        </div>
                        <span>Disapproved</span>
                    </div>
                </div>

                <!-- GSO signature (Line is on top of JOEL A. TUMAMAO) -->
                <div class="flex flex-col items-center mt-8">
                    <div class="h-6"></div>
                    <div class="w-full border-t border-black text-center pt-1">
                        <span class="text-sm font-extrabold text-black uppercase tracking-wide">JOEL A. TUMAMAO</span>
                    </div>
                    <span class="text-[10px] text-black uppercase font-semibold">GSO</span>
                </div>
            </div>
        </div>

        <!-- Document Metadata footer details -->
        <div class="pt-4 flex justify-between items-center text-[10px] text-black font-mono border-t border-black mt-6">
            <span>F - GSO - 61207</span>
            <span>Rev. No. 00, October 23, 2025</span>
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
            const originalText = btnText ? btnText.textContent : 'Download PDF File';

            if (btn) btn.disabled = true;
            if (btnText) btnText.innerHTML = '<span class="inline-block animate-spin mr-1">⏳</span> Generating...';

            const element = document.querySelector('.print-container');
            const isMobile = /Android|iPhone|iPad|iPod|webOS|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

            const opt = {
                margin:       0.3,
                filename:     'Vehicle-Request-Form-{{ $request->request_number }}.pdf',
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
