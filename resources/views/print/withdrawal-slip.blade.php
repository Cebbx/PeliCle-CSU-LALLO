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
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        @media print {
            body {
                background: white !important;
                color: black !important;
                font-size: 13px;
                padding: 0 !important;
                margin: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            @page {
                size: letter; /* Force US Letter paper size */
                margin: 0.4in;
            }
            .print-container {
                padding: 0 !important;
                margin: 0 !important;
                min-height: auto !important;
                height: auto !important;
                box-shadow: none !important;
                border: none !important;
                max-width: 100% !important;
                width: 100% !important;
            }
        }
    </style>
</head>
<body class="bg-gray-100 py-3 sm:py-6 px-2 sm:px-4">

    <!-- Floating Top Bar (Sticky, Mobile Responsive, hidden on print) -->
    <div class="no-print sticky top-2 z-50 max-w-[7.5in] mx-auto mb-4 bg-white/95 backdrop-blur-sm p-3 sm:p-4 rounded-xl shadow-lg border border-gray-200 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="text-gray-800 font-extrabold text-xs sm:text-sm tracking-tight">Gasoline Withdrawal Slip</span>
            <span class="text-[10px] sm:text-xs text-gray-500 font-mono hidden sm:inline">({{ $slip->slip_number }})</span>
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
    <div class="bg-white w-full max-w-[7.5in] mx-auto p-4 sm:p-10 shadow-md sm:shadow-lg border border-gray-200 flex flex-col justify-between print-container box-border">
        <div>
            <!-- Header (matching official CSU layout) -->
            <div class="text-center mb-1 pt-2 sm:pt-4">
                <p class="text-[11px] sm:text-[12px] uppercase tracking-wider text-black font-medium leading-tight">Republic of the Philippines</p>
                <h1 class="text-sm sm:text-[16px] font-bold uppercase text-black tracking-wide mt-0.5 leading-tight">Cagayan State University</h1>
                <p class="text-[11px] sm:text-[12px] text-black italic mt-0.5">Lal-lo Campus</p>
            </div>

            <!-- Control No. and Date (right-aligned underneath header) -->
            <div class="flex justify-end text-xs sm:text-[13px] mb-4 sm:mb-8 mt-2">
                <div class="space-y-1 text-right">
                    <p>Control No.: <span class="border-b border-black font-bold px-2 sm:px-3 inline-block min-w-[110px] sm:min-w-[130px] text-center">{{ $slip->slip_number }}</span></p>
                    <p>Date: <span class="border-b border-black px-2 sm:px-3 inline-block min-w-[110px] sm:min-w-[130px] text-center">{{ $slip->created_at->format('F d, Y') }}</span></p>
                </div>
            </div>

            <!-- Subject Details -->
            <div class="text-xs sm:text-[14px] text-black space-y-3 sm:space-y-4 mb-4 sm:mb-6">
                <div>
                    <p class="font-bold">The Manager</p>
                    <p>Shell Service Station</p>
                    <p>Bagumbayan, Lal-lo, Cagayan</p>
                </div>

                <div>
                    <p>Sir/ Madam:</p>
                </div>

                <!-- Authorization Paragraph -->
                <div class="text-xs sm:text-[14px] text-black leading-relaxed sm:leading-[2.4rem] text-left pt-1 mb-4 sm:mb-6">
                    This is to AUTHORIZE <span class="border-b border-black font-bold px-2 sm:px-3 inline-block min-w-[160px] sm:min-w-[200px] text-center leading-normal pb-0.5">{!! !empty(trim($driverName ?? '')) ? e($driverName) : '&nbsp;' !!}</span> official driver of <span class="border-b border-black font-bold px-2 sm:px-3 inline-block min-w-[120px] sm:min-w-[140px] text-center leading-normal pb-0.5">{!! !empty(trim($vehicleModel ?? '')) ? e($vehicleModel) : '&nbsp;' !!}</span> with plate No. <span class="border-b border-black font-bold px-2 sm:px-3 inline-block min-w-[90px] sm:min-w-[110px] text-center leading-normal pb-0.5">{!! !empty(trim($vehiclePlate ?? '')) ? e($vehiclePlate) : '&nbsp;' !!}</span> to withdraw the following:
                </div>
            </div>

            @if(is_string($items) && !empty(trim($items)))
                <!-- Raw text layout (notes submitted from driver portal or seeded string) -->
                <div class="pl-2 sm:pl-12 text-xs sm:text-[14px] text-black mb-6 sm:mb-8 leading-relaxed">
                    <strong class="block mb-2 text-slate-800">Requested Items/Notes:</strong>
                    <div class="border border-dashed border-gray-400 p-3 sm:p-4 rounded-lg bg-gray-50/50 whitespace-pre-line">
                        {{ $items }}
                    </div>
                </div>
            @else
                <!-- Items Table/List (matching the image layout perfectly) -->
                <div class="pl-2 sm:pl-12 text-xs sm:text-[14px] text-black space-y-2 sm:space-y-2.5 mb-6 sm:mb-8">
                    <div class="flex items-center gap-2 sm:gap-4">
                        <span class="border-b border-black font-bold text-center min-w-[4.5rem] sm:min-w-[5.5rem] px-2 inline-block h-6 pb-0.5">{!! !empty(trim($diesel ?? '')) ? e($diesel) : '&nbsp;' !!}</span>
                        <span>No. of Liters of Diesel</span>
                    </div>
                    <div class="flex items-center gap-2 sm:gap-4">
                        <span class="border-b border-black font-bold text-center min-w-[4.5rem] sm:min-w-[5.5rem] px-2 inline-block h-6 pb-0.5">{!! !empty(trim($gasRegular ?? '')) ? e($gasRegular) : '&nbsp;' !!}</span>
                        <span>No. of Liters of Gasoline (Regular)</span>
                    </div>
                    <div class="flex items-center gap-2 sm:gap-4">
                        <span class="border-b border-black font-bold text-center min-w-[4.5rem] sm:min-w-[5.5rem] px-2 inline-block h-6 pb-0.5">{!! !empty(trim($gasPremium ?? '')) ? e($gasPremium) : '&nbsp;' !!}</span>
                        <span>No. of Liters of Gasoline (Premium)</span>
                    </div>
                    <div class="flex items-center gap-2 sm:gap-4">
                        <span class="border-b border-black font-bold text-center min-w-[4.5rem] sm:min-w-[5.5rem] px-2 inline-block h-6 pb-0.5">{!! !empty(trim($lubricant40 ?? '')) ? e($lubricant40) : '&nbsp;' !!}</span>
                        <span>No. of Liters of Lubricant Oil 40</span>
                    </div>
                    <div class="flex items-center gap-2 sm:gap-4">
                        <span class="border-b border-black font-bold text-center min-w-[4.5rem] sm:min-w-[5.5rem] px-2 inline-block h-6 pb-0.5">{!! !empty(trim($lubricant30 ?? '')) ? e($lubricant30) : '&nbsp;' !!}</span>
                        <span>No. of Liters of Lubricant Oil 30</span>
                    </div>
                    <div class="flex items-center gap-2 sm:gap-4">
                        <span class="border-b border-black font-bold text-center min-w-[4.5rem] sm:min-w-[5.5rem] px-2 inline-block h-6 pb-0.5">{!! !empty(trim($brakeFluid ?? '')) ? e($brakeFluid) : '&nbsp;' !!}</span>
                        <span>No. of Liters of Brake Fluid</span>
                    </div>
                    <div class="flex items-center gap-2 sm:gap-4">
                        <span class="border-b border-black font-bold text-center min-w-[4.5rem] sm:min-w-[5.5rem] px-2 inline-block h-6 pb-0.5">{!! !empty(trim($grease ?? '')) ? e($grease) : '&nbsp;' !!}</span>
                        <span>No. of Liters of <span class="underline underline-offset-2 font-semibold">2T</span>&nbsp;&nbsp;<span class="underline underline-offset-2 font-semibold">Grease</span>&nbsp;&nbsp;<span class="underline underline-offset-2 font-semibold">ATF</span></span>
                    </div>
                    <div class="flex items-center gap-2 sm:gap-4">
                        <span class="border-b border-black font-bold text-center min-w-[4.5rem] sm:min-w-[5.5rem] px-2 inline-block h-6 pb-0.5">{!! !empty(trim($gearOil ?? '')) ? e($gearOil) : '&nbsp;' !!}</span>
                        <span>No. of Liters of Gear Oil</span>
                    </div>
                </div>
            @endif
        </div>

        <!-- Footer Signatures (staggered stack to match photo exactly) -->
        <div class="mt-4">
            <div class="space-y-6 sm:space-y-8 text-xs sm:text-[14px] text-black">
                <!-- Prepared By (Left aligned, name indented) -->
                <div class="text-left w-full pl-2 sm:pl-4">
                    <p>Prepared by:</p>
                    <div class="pl-6 sm:pl-12 mt-3 sm:mt-4">
                        <p class="font-bold uppercase tracking-wide">JOEL A. TUMAMAO</p>
                        <p class="text-[11px] sm:text-[13px] text-gray-800">General Services Officer</p>
                    </div>
                </div>

                <!-- Approved By (Left aligned, name indented) -->
                <div class="text-left w-full pl-2 sm:pl-4">
                    <p>Approved by:</p>
                    <div class="pl-6 sm:pl-12 mt-3 sm:mt-4">
                        <p class="font-bold uppercase tracking-wide">ENGR. JAMES B. CABILDO, ASEAN ENGR.</p>
                        <p class="text-[11px] sm:text-[13px] text-gray-800">Campus Executive Officer</p>
                    </div>
                </div>
            </div>

            <!-- Form Code and Revision number at the bottom -->
            <div class="mt-6 sm:mt-8 flex justify-between text-[9px] sm:text-[10px] text-black font-mono border-t border-black pt-3">
                <span>F - GSO - 61203</span>
                <span>Rev. No.: 01: 10-20-2025</span>
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

            const element = document.querySelector('.print-container');
            const filename = 'Gasoline-Withdrawal-Slip-{{ $slip->slip_number }}.pdf';

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
            clone.style.padding = '32px 48px';
            clone.style.boxShadow = 'none';
            clone.style.border = 'none';
            clone.style.backgroundColor = '#ffffff';
            clone.style.boxSizing = 'border-box';

            offscreen.appendChild(clone);
            document.body.appendChild(offscreen);

            // 1-Page Guarantee: Letter paper height at 816px width is 1056px.
            if (clone.offsetHeight > 1040) {
                clone.style.padding = '16px 36px';
                if (clone.offsetHeight > 1040) {
                    const ratio = 1030 / clone.offsetHeight;
                    clone.style.transform = `scale(${ratio})`;
                    clone.style.transformOrigin = 'top center';
                    offscreen.style.height = '1035px';
                    offscreen.style.overflow = 'hidden';
                }
            }

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
                pagebreak: { mode: 'legacy' }
            };

            try {
                const pdfWorker = html2pdf().set(opt).from(clone);
                const pdf = await pdfWorker.toPdf().get('pdf');

                const totalPages = pdf.internal.getNumberOfPages();
                if (totalPages > 1) {
                    for (let p = totalPages; p > 1; p--) {
                        pdf.deletePage(p);
                    }
                }

                const blob = pdf.output('blob');
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
