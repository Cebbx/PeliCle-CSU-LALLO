<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gasoline Withdrawal Slip - {{ $slip->slip_number }}</title>
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
                font-size: 13px;
            }
            .no-print {
                display: none !important;
            }
            @page {
                size: letter; /* Force US Letter paper size */
                margin: 0.5in;
            }
            .print-container {
                padding: 0 !important;
                margin: 0 !important;
                min-height: auto !important;
                height: auto !important;
                box-shadow: none !important;
                border: none !important;
            }
        }
    </style>
</head>
@php
    $backUrl = url()->previous();
    if ($backUrl === url()->current() || empty($backUrl)) {
        $backUrl = url('/admin/withdrawal-slips');
    }
@endphp
<body class="bg-gray-100 py-4 sm:py-6 px-2 sm:px-4">

    <!-- In-App Browser (Messenger / IG) Notification Banner -->
    <div id="inAppNotice" class="no-print hidden max-w-[7.5in] mx-auto mb-3 bg-amber-50 border border-amber-300 rounded-xl p-3 sm:p-3.5 shadow-sm text-xs text-amber-900 flex items-start justify-between gap-3">
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
    <div class="no-print max-w-[7.5in] mx-auto mb-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between bg-white p-3 sm:p-4 rounded-xl shadow-md border border-gray-200 gap-3">
        <div class="flex items-center justify-between sm:justify-start gap-3">
            <a href="{{ $backUrl }}" onclick="handleBack(event)" class="bg-gray-100 hover:bg-gray-200 active:bg-gray-300 text-gray-700 font-bold py-2 px-3 sm:px-3.5 rounded-lg transition-colors flex items-center gap-1.5 text-xs sm:text-sm border border-gray-300 shadow-sm cursor-pointer shrink-0" title="Go Back">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                <span>Back</span>
            </a>
            <span class="text-gray-700 font-bold text-xs sm:text-sm truncate">Gasoline Withdrawal Slip</span>
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
    <div class="overflow-x-auto w-full max-w-[7.5in] mx-auto pb-4">
        <!-- Main Form Paper Sheet (8.5in x 11in standard proportions) -->
        <div class="bg-white w-full max-w-[7.5in] mx-auto p-4 sm:p-8 border border-black shadow-lg flex flex-col justify-between print-container" style="min-height: 9.5in; min-width: 680px;">

    @php
        $items = $slip->requested_items ?? [];
        
        // If it's a string, try to decode it as JSON first (just in case it's a JSON-encoded array string)
        if (is_string($items)) {
            $decoded = json_decode($items, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $items = $decoded;
            }
        }

        // Helper to safely fetch value from array items (supports both repeater list and assoc array)
        $getVal = function ($key) use ($items) {
            if (is_array($items)) {
                // Check direct assoc key
                if (isset($items[$key]) && !is_array($items[$key]) && filled($items[$key])) {
                    return (string) $items[$key];
                }
                // Check repeater array list: [['item' => 'diesel', 'quantity' => 30], ...]
                foreach ($items as $row) {
                    if (is_array($row) && isset($row['item']) && $row['item'] === $key && !empty($row['quantity'])) {
                        return (string) $row['quantity'];
                    }
                }
            }
            return '';
        };

        $diesel = $getVal('diesel');
        $gasRegular = $getVal('gasoline_regular');
        $gasPremium = $getVal('gasoline_premium');
        $lubricant40 = $getVal('lubricant_40');
        $lubricant30 = $getVal('lubricant_30');
        $brakeFluid = $getVal('brake_fluid');
        $grease = $getVal('grease_atf');
        $gearOil = $getVal('gear_oil');
    @endphp

    <!-- Main Ticket Sheet -->
    <div class="bg-white w-full max-w-[7.5in] mx-auto p-8 sm:p-12 shadow-lg border border-gray-200 flex flex-col justify-between print-container" style="min-height: 10.0in;">
        <div>
            <!-- Header (matching official CSU layout) -->
            <div class="text-center mb-1 pt-4">
                <p class="text-[12px] uppercase tracking-wider text-black font-medium">Republic of the Philippines</p>
                <h1 class="text-[16px] font-bold uppercase text-black tracking-wide mt-0.5">Cagayan State University</h1>
                <p class="text-[12px] text-black italic mt-0.5">Lal-lo, Campus</p>
            </div>

            <!-- Control No. and Date (right-aligned underneath header) -->
            <div class="flex justify-end text-[13px] mb-8 mt-2">
                <div class="space-y-1">
                    <p>Control No.: <span class="border-b border-black font-bold px-3 inline-block min-w-[130px] text-center">{{ $slip->slip_number }}</span></p>
                    <p>Date: <span class="border-b border-black px-3 inline-block min-w-[130px] text-center">{{ $slip->created_at->format('F d, Y') }}</span></p>
                </div>
            </div>

            <!-- Subject Details -->
            <div class="text-[14px] text-black space-y-4 mb-6">
                <div>
                    <p class="font-bold">The Manager</p>
                    <p>Shell Service Station</p>
                    <p>Bagumbayan, Lal-lo, Cagayan</p>
                </div>

                <div>
                    <p>Sir/ Madam:</p>
                </div>

                <!-- Authorization Paragraph -->
                <div class="leading-relaxed text-justify pt-1">
                    This is to AUTHORIZE <span class="border-b border-black font-bold px-3 inline-block min-w-[280px] text-center">{{ $driverName ?? ($slip->driver_name ?: ($slip->tripTicket?->driver?->name ?? '_______________')) }}</span> official driver 
                    of <span class="border-b border-black font-bold px-3 inline-block min-w-[160px] text-center">{{ $vehicleModel ?: '_______________' }}</span> with plate No. <span class="border-b border-black font-bold px-3 inline-block min-w-[150px] text-center">{{ $vehiclePlate ?: '_______________' }}</span> to withdraw the following:
                </div>
            </div>

            @if(is_string($items) && !empty(trim($items)))
                <!-- Raw text layout (notes submitted from driver portal or seeded string) -->
                <div class="pl-16 text-[14px] text-black mb-8 leading-relaxed">
                    <strong class="block mb-2 text-slate-800">Requested Items/Notes:</strong>
                    <div class="border border-dashed border-gray-400 p-4 rounded-lg bg-gray-50/50 whitespace-pre-line">
                        {{ $items }}
                    </div>
                </div>
            @else
                <!-- Items Table/List (matching the image layout perfectly) -->
                <div class="pl-16 text-[14px] text-black space-y-2.5 mb-8">
                    <div class="flex items-center gap-4">
                        <span class="border-b border-black font-bold text-center w-16 inline-block h-6 pb-0.5">{{ $diesel }}</span>
                        <span>No. of Liters of Diesel</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="border-b border-black font-bold text-center w-16 inline-block h-6 pb-0.5">{{ $gasRegular }}</span>
                        <span>No. of Liters of Gasoline (Regular)</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="border-b border-black font-bold text-center w-16 inline-block h-6 pb-0.5">{{ $gasPremium }}</span>
                        <span>No. of Liters of Gasoline (Premium)</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="border-b border-black font-bold text-center w-16 inline-block h-6 pb-0.5">{{ $lubricant40 }}</span>
                        <span>No. of Liters of Lubricant Oil 40</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="border-b border-black font-bold text-center w-16 inline-block h-6 pb-0.5">{{ $lubricant30 }}</span>
                        <span>No. of Liters of Lubricant Oil 30</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="border-b border-black font-bold text-center w-16 inline-block h-6 pb-0.5">{{ $brakeFluid }}</span>
                        <span>No. of Liters of Brake Fluid</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="border-b border-black font-bold text-center w-16 inline-block h-6 pb-0.5">{{ $grease }}</span>
                        <span>No. of Liters of <span class="underline underline-offset-2 font-semibold">2T</span>&nbsp;&nbsp;<span class="underline underline-offset-2 font-semibold">Grease</span>&nbsp;&nbsp;<span class="underline underline-offset-2 font-semibold">ATF</span></span>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="border-b border-black font-bold text-center w-16 inline-block h-6 pb-0.5">{{ $gearOil }}</span>
                        <span>No. of Liters of Gear Oil</span>
                    </div>
                </div>
            @endif
        </div>

        <!-- Footer Signatures (staggered stack to match photo exactly) -->
        <div class="mt-4">
            <div class="space-y-8 text-[14px] text-black">
                <!-- Prepared By (Left aligned, name indented) -->
                <div class="text-left w-full pl-4">
                    <p>Prepared by:</p>
                    <div class="pl-12 mt-4">
                        <p class="font-bold uppercase tracking-wide">JOEL A. TUMAMAO</p>
                        <p class="text-[13px] text-gray-800">General Services Officer</p>
                    </div>
                </div>

                <!-- Approved By (Left aligned, name indented) -->
                <div class="text-left w-full pl-4">
                    <p>Approved by:</p>
                    <div class="pl-12 mt-4">
                        <p class="font-bold uppercase tracking-wide">ENGR. JAMES B. CABILDO, ASEAN ENGR.</p>
                        <p class="text-[13px] text-gray-800">Campus Executive Officer</p>
                    </div>
                </div>
            </div>

            <!-- Form Code and Revision number at the bottom -->
            <div class="mt-8 flex justify-between text-[10px] text-black font-mono">
                <span>F - GSO - 61203</span>
                <span>Rev. No.: 01: 10-20-2025</span>
            </div>
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
                filename:     'Gasoline-Withdrawal-Slip-{{ $slip->slip_number }}.pdf',
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
