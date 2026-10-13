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
            background-color: #f3f4f6;
            color: black;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Screen Document Wrapper */
        .sheet-outer {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            padding-bottom: 2rem;
        }

        .withdrawal-slip-doc {
            width: 816px; /* Exactly 8.5 inches at 96 DPI */
            min-width: 816px;
            max-width: 816px;
            background: #ffffff;
            margin: 0 auto;
            padding: 40px 48px;
            box-sizing: border-box;
            border: 1px solid #d1d5db;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        @media print {
            body {
                background: white !important;
                color: black !important;
                font-size: 13px;
                padding: 0 !important;
                margin: 0 !important;
                overflow: visible !important;
            }
            .no-print {
                display: none !important;
            }
            .sheet-outer {
                overflow: visible !important;
                padding: 0 !important;
                margin: 0 !important;
                display: block !important;
                width: 100% !important;
            }
            .withdrawal-slip-doc {
                min-height: 0 !important;
                height: auto !important;
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
                min-width: 0 !important;
                width: 100% !important;
                overflow: visible !important;
            }
            @page {
                size: letter portrait;
                margin: 0.35in 0.4in;
            }
        }
    </style>
</head>
<body class="bg-gray-100 py-3 sm:py-6 px-2 sm:px-4">

    <!-- Floating Top Bar (Sticky, Mobile Responsive, hidden on print) -->
    <div class="no-print sticky top-2 z-50 max-w-[816px] mx-auto mb-3 bg-white/95 backdrop-blur-sm p-3 sm:p-4 rounded-xl shadow-lg border border-gray-200 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="text-gray-800 font-extrabold text-xs sm:text-sm tracking-tight">Gasoline Withdrawal Slip</span>
            <span class="text-xs text-gray-500 font-mono">({{ $slip->slip_number }})</span>
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
            <!-- Back Button -->
            <button onclick="if (window.history.length > 1) { window.history.back(); } else { window.close(); }" class="flex-1 sm:flex-initial bg-gray-100 hover:bg-gray-200 active:scale-95 text-gray-700 font-bold py-2 px-3 sm:px-4 rounded-lg transition-all flex items-center justify-center gap-1.5 text-xs sm:text-sm shadow-sm border border-gray-300 cursor-pointer select-none">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Back</span>
            </button>
            <button id="btnDownloadPdf" onclick="downloadPDF(this)" class="flex-1 sm:flex-initial bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold py-2 px-3 sm:px-4 rounded-lg transition-all flex items-center justify-center gap-1.5 text-xs sm:text-sm shadow-md cursor-pointer select-none">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Download PDF File</span>
            </button>
            <button id="btnPrintDoc" onclick="triggerPrint(this)" class="flex-1 sm:flex-initial bg-sky-600 hover:bg-sky-700 active:scale-95 text-white font-bold py-2 px-3 sm:px-4 rounded-lg transition-all flex items-center justify-center gap-1.5 text-xs sm:text-sm shadow-md cursor-pointer select-none">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Print Document</span>
            </button>
        </div>
    </div>

    <!-- Mobile Scroll Hint (Visible only on narrow screens, hidden on print) -->
    <div class="no-print block md:hidden max-w-[816px] mx-auto mb-3 px-3 py-1.5 bg-blue-50 border border-blue-200 rounded-lg text-blue-700 text-xs flex items-center justify-between">
        <span class="flex items-center gap-1.5">
            <svg class="w-4 h-4 flex-shrink-0 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 1118 0z"/>
            </svg>
            <span>Scroll left/right to view full preview. Tap <strong>Download PDF File</strong> for the full Letter printout.</span>
        </span>
    </div>

    @php
        $items = $slip->requested_items ?? [];
        
        if (is_string($items)) {
            $decoded = json_decode($items, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $items = $decoded;
            }
        }

        $getVal = function ($key) use ($items) {
            if (is_array($items)) {
                if (isset($items[$key]) && !is_array($items[$key]) && filled($items[$key])) {
                    return (string) $items[$key];
                }
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

    <!-- Scrollable Screen Wrapper -->
    <div class="sheet-outer">
        <!-- Main Ticket Sheet (Strict 8.5in desktop Letter format) -->
        <div class="withdrawal-slip-doc flex flex-col justify-between">
            <div>
                <!-- Header (matching official CSU layout) -->
                <div class="text-center mb-1 pt-2">
                    <p class="text-xs uppercase tracking-wider text-black font-medium leading-tight">Republic of the Philippines</p>
                    <h1 class="text-base font-bold uppercase text-black tracking-wide mt-0.5 leading-tight">Cagayan State University</h1>
                    <p class="text-xs text-black italic mt-0.5">Lal-lo Campus</p>
                </div>

                <!-- Control No. and Date (right-aligned underneath header) -->
                <div class="flex justify-end text-sm mb-6 mt-2">
                    <div class="space-y-1 text-right">
                        <p>Control No.: <span class="border-b border-black font-bold px-3 inline-block min-w-[130px] text-center">{{ $slip->slip_number }}</span></p>
                        <p>Date: <span class="border-b border-black px-3 inline-block min-w-[130px] text-center">{{ $slip->created_at->format('F d, Y') }}</span></p>
                    </div>
                </div>

                <!-- Subject Details -->
                <div class="text-sm text-black space-y-4 mb-6">
                    <div>
                        <p class="font-bold">The Manager</p>
                        <p>Shell Service Station</p>
                        <p>Bagumbayan, Lal-lo, Cagayan</p>
                    </div>

                    <div>
                        <p>Sir/ Madam:</p>
                    </div>

                    <!-- Authorization Paragraph -->
                    <div class="text-sm text-black leading-[2.4rem] text-left pt-1 mb-6">
                        This is to AUTHORIZE <span class="border-b border-black font-bold px-3 inline-block min-w-[200px] text-center leading-normal pb-0.5">{!! !empty(trim($driverName ?? '')) ? e($driverName) : '&nbsp;' !!}</span> official driver of <span class="border-b border-black font-bold px-3 inline-block min-w-[140px] text-center leading-normal pb-0.5">{!! !empty(trim($vehicleModel ?? '')) ? e($vehicleModel) : '&nbsp;' !!}</span> with plate No. <span class="border-b border-black font-bold px-3 inline-block min-w-[110px] text-center leading-normal pb-0.5">{!! !empty(trim($vehiclePlate ?? '')) ? e($vehiclePlate) : '&nbsp;' !!}</span> to withdraw the following:
                    </div>
                </div>

                @if(is_string($items) && !empty(trim($items)))
                    <!-- Raw text layout (notes submitted from driver portal or seeded string) -->
                    <div class="pl-12 text-sm text-black mb-8 leading-relaxed">
                        <strong class="block mb-2 text-slate-800">Requested Items/Notes:</strong>
                        <div class="border border-dashed border-gray-400 p-4 rounded-lg bg-gray-50/50 whitespace-pre-line">
                            {{ $items }}
                        </div>
                    </div>
                @else
                    <!-- Items Table/List (matching the image layout perfectly) -->
                    <div class="pl-12 text-sm text-black space-y-2.5 mb-8">
                        <div class="flex items-center gap-4">
                            <span class="border-b border-black font-bold text-center min-w-[5.5rem] px-2 inline-block h-6 pb-0.5">{!! !empty(trim($diesel ?? '')) ? e($diesel) : '&nbsp;' !!}</span>
                            <span>No. of Liters of Diesel</span>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="border-b border-black font-bold text-center min-w-[5.5rem] px-2 inline-block h-6 pb-0.5">{!! !empty(trim($gasRegular ?? '')) ? e($gasRegular) : '&nbsp;' !!}</span>
                            <span>No. of Liters of Gasoline (Regular)</span>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="border-b border-black font-bold text-center min-w-[5.5rem] px-2 inline-block h-6 pb-0.5">{!! !empty(trim($gasPremium ?? '')) ? e($gasPremium) : '&nbsp;' !!}</span>
                            <span>No. of Liters of Gasoline (Premium)</span>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="border-b border-black font-bold text-center min-w-[5.5rem] px-2 inline-block h-6 pb-0.5">{!! !empty(trim($lubricant40 ?? '')) ? e($lubricant40) : '&nbsp;' !!}</span>
                            <span>No. of Liters of Lubricant Oil 40</span>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="border-b border-black font-bold text-center min-w-[5.5rem] px-2 inline-block h-6 pb-0.5">{!! !empty(trim($lubricant30 ?? '')) ? e($lubricant30) : '&nbsp;' !!}</span>
                            <span>No. of Liters of Lubricant Oil 30</span>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="border-b border-black font-bold text-center min-w-[5.5rem] px-2 inline-block h-6 pb-0.5">{!! !empty(trim($brakeFluid ?? '')) ? e($brakeFluid) : '&nbsp;' !!}</span>
                            <span>No. of Liters of Brake Fluid</span>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="border-b border-black font-bold text-center min-w-[5.5rem] px-2 inline-block h-6 pb-0.5">{!! !empty(trim($grease ?? '')) ? e($grease) : '&nbsp;' !!}</span>
                            <span>No. of Liters of <span class="underline underline-offset-2 font-semibold">2T</span>&nbsp;&nbsp;<span class="underline underline-offset-2 font-semibold">Grease</span>&nbsp;&nbsp;<span class="underline underline-offset-2 font-semibold">ATF</span></span>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="border-b border-black font-bold text-center min-w-[5.5rem] px-2 inline-block h-6 pb-0.5">{!! !empty(trim($gearOil ?? '')) ? e($gearOil) : '&nbsp;' !!}</span>
                            <span>No. of Liters of Gear Oil</span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Footer Signatures (staggered stack to match photo exactly) -->
            <div class="mt-4">
                <div class="space-y-8 text-sm text-black">
                    @php
                        $gsoName = \App\Models\CampusSetting::get('gso_name', 'JOEL A. TUMAMAO');
                        $gsoTitle = \App\Models\CampusSetting::get('gso_title', 'General Services Officer');
                        $ceoName = \App\Models\CampusSetting::get('ceo_name', 'ENGR. JAMES B. CABILDO, ASEAN ENGR.');
                        $ceoTitle = \App\Models\CampusSetting::get('ceo_title', 'Campus Executive Officer');
                    @endphp
                    <!-- Prepared By (Left aligned, name indented) -->
                    <div class="text-left w-full pl-4">
                        <p>Prepared by:</p>
                        <div class="pl-12 mt-4">
                            <p class="font-bold uppercase tracking-wide">{{ $gsoName }}</p>
                            <p class="text-xs text-gray-800">{{ $gsoTitle }}</p>
                        </div>
                    </div>

                    <!-- Approved By (Left aligned, name indented) -->
                    <div class="text-left w-full pl-4">
                        <p>Approved by:</p>
                        <div class="pl-12 mt-4">
                            <p class="font-bold uppercase tracking-wide">{{ $ceoName }}</p>
                            <p class="text-xs text-gray-800">{{ $ceoTitle }}</p>
                        </div>
                    </div>
                </div>

                <!-- Form Code and Revision number at the bottom -->
                <div class="mt-8 flex justify-between text-[10px] text-black font-mono border-t border-black pt-3">
                    <span>F - GSO - 61203</span>
                    <span>Rev. No.: 01: 10-20-2025</span>
                </div>
            </div>
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

            // Immediately ensure page scroll is at origin
            window.scrollTo(0, 0);

            const sourceDoc = document.querySelector('.withdrawal-slip-doc');
            const filename = 'Gasoline-Withdrawal-Slip-{{ $slip->slip_number }}.pdf';

            // Create offscreen container fixed to 816px (standard 8.5in at 96 DPI)
            const offscreen = document.createElement('div');
            offscreen.style.position = 'fixed';
            offscreen.style.left = '-10000px';
            offscreen.style.top = '0';
            offscreen.style.width = '816px';
            offscreen.style.minWidth = '816px';
            offscreen.style.maxWidth = '816px';
            offscreen.style.backgroundColor = '#ffffff';
            offscreen.style.zIndex = '-9999';
            offscreen.style.overflow = 'visible';

            const clone = sourceDoc.cloneNode(true);
            clone.style.width = '816px';
            clone.style.minWidth = '816px';
            clone.style.maxWidth = '816px';
            clone.style.margin = '0';
            clone.style.padding = '36px 44px';
            clone.style.boxShadow = 'none';
            clone.style.border = 'none';
            clone.style.backgroundColor = '#ffffff';

            offscreen.appendChild(clone);
            document.body.appendChild(offscreen);

            const opt = {
                margin:       [0.25, 0.25, 0.25, 0.25], // top, left, bottom, right in inches
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
            const sheetOuters = document.querySelectorAll('.sheet-outer');
            sheetOuters.forEach(el => el.scrollLeft = 0);
            window.print();
        }

        window.addEventListener('beforeprint', () => {
            window.scrollTo(0, 0);
            const sheetOuters = document.querySelectorAll('.sheet-outer');
            sheetOuters.forEach(el => el.scrollLeft = 0);
        });
    </script>

</body>
</html>
