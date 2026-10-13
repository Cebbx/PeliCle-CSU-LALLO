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

        .request-form-doc {
            width: 816px; /* Exactly 8.5 inches at 96 DPI */
            min-width: 816px;
            max-width: 816px;
            background: #ffffff;
            margin: 0 auto;
            padding: 36px 40px;
            box-sizing: border-box;
            border: 1px solid #d1d5db;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        @media print {
            body {
                background: white !important;
                color: black !important;
                font-size: 11px;
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
            .request-form-doc {
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
                margin: 0.3in 0.35in;
            }
        }
    </style>
</head>
<body class="bg-gray-100 py-3 sm:py-6 px-2 sm:px-4">

    <!-- Floating Top Bar (Sticky, Mobile Responsive, hidden on print) -->
    <div class="no-print sticky top-2 z-50 max-w-[816px] mx-auto mb-3 bg-white/95 backdrop-blur-sm p-3 sm:p-4 rounded-xl shadow-lg border border-gray-200 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="text-gray-800 font-extrabold text-xs sm:text-sm tracking-tight">Vehicle Request Form</span>
            <span class="text-xs text-gray-500 font-mono">({{ $request->request_number }})</span>
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

    <!-- Scrollable Screen Wrapper -->
    <div class="sheet-outer">
        <!-- Main Form Page Sheet (Strict 8.5in desktop Letter format) -->
        <div class="request-form-doc flex flex-col">
            <!-- Header (Flexbox layout - Centered on Mobile, Desktop & Print) -->
            <div class="w-full border-b-2 border-black pb-3 mb-4 flex items-center justify-center gap-5">
                <img src="{{ asset('csu-logo.png') }}" alt="CSU Logo" crossorigin="anonymous" class="w-16 h-16 object-contain flex-shrink-0" onerror="this.src='/csu-logo-sm.png'" />
                <div class="text-center">
                    <h2 class="text-xs uppercase tracking-wider text-black font-medium leading-tight">Republic of the Philippines</h2>
                    <h1 class="text-base font-extrabold uppercase text-black tracking-wide mt-0.5 leading-tight">Cagayan State University</h1>
                    <h3 class="text-xs text-black mt-0.5 font-medium leading-tight">Lal-lo Campus</h3>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-black mt-0.5 leading-tight">General Service Office</h2>
                </div>
            </div>

            <!-- Title -->
            <div class="text-center mb-4">
                <h1 class="text-base font-extrabold tracking-wide uppercase border-b border-black inline-block pb-0.5">Vehicle Request Form</h1>
            </div>

            <!-- Request Details Table Grid -->
            <div class="border border-black mb-6 text-sm">
                <!-- Row 1 -->
                <div class="grid grid-cols-12 border-b border-black">
                    <div class="col-span-8 p-3 border-r border-black flex flex-col justify-between" style="min-height: 60px;">
                        <span class="text-[10px] uppercase font-bold text-black">Client:</span>
                        <span class="text-sm font-bold text-black text-center mt-0.5 border-b border-gray-300 w-11/12 mx-auto">{{ $request->employee_name }}</span>
                        <span class="text-[9px] text-black text-center mt-0.5">Name</span>
                    </div>
                    <div class="col-span-4 p-3 flex flex-col justify-between" style="min-height: 60px;">
                        <span class="text-[10px] uppercase font-bold text-black">Date:</span>
                        <span class="text-sm font-bold text-black text-center mt-0.5 border-b border-gray-300 w-11/12 mx-auto">{{ \Carbon\Carbon::parse($request->created_at)->format('M d, Y') }}</span>
                        <span class="text-[9px] text-black text-center mt-0.5 truncate">Office: <span class="font-bold text-black">{{ $request->department }}</span></span>
                    </div>
                </div>

                <!-- Row 2 -->
                <div class="grid grid-cols-12 border-b border-black">
                    <div class="col-span-6 p-3 border-r border-black flex flex-col" style="min-height: 48px;">
                        <span class="text-[10px] uppercase font-bold text-black">Date of Travel:</span>
                        <span class="text-sm font-bold text-black mt-0.5">{{ \Carbon\Carbon::parse($request->date)->format('F d, Y') }}</span>
                    </div>
                    <div class="col-span-6 p-3 flex flex-col" style="min-height: 48px;">
                        <span class="text-[10px] uppercase font-bold text-black">Departure Time:</span>
                        <span class="text-sm font-bold text-black mt-0.5">{{ \Carbon\Carbon::parse($request->time)->format('g:i A') }}</span>
                    </div>
                </div>

                <!-- Row 3 -->
                <div class="grid grid-cols-12 border-b border-black">
                    <div class="col-span-12 p-3 flex flex-col" style="min-height: 48px;">
                        <span class="text-[10px] uppercase font-bold text-black">Destination:</span>
                        <span class="text-sm font-bold text-black mt-0.5">{{ $request->destination }}</span>
                    </div>
                </div>

                <!-- Row 4 -->
                <div class="grid grid-cols-12 border-b border-black">
                    <div class="col-span-12 p-3 flex flex-col" style="min-height: 60px;">
                        <span class="text-[10px] uppercase font-bold text-black">Purpose of Travel:</span>
                        <span class="text-sm font-medium text-black mt-0.5 leading-relaxed">{{ $request->purpose }}</span>
                    </div>
                </div>

                <!-- Row 5 (Passengers) -->
                <div class="grid grid-cols-12">
                    <div class="col-span-3 p-3 border-r border-black flex items-center justify-center">
                        <span class="text-[10px] uppercase font-bold text-black text-center leading-tight">Name of Passengers</span>
                    </div>
                    <div class="col-span-9 p-3 flex flex-col gap-1.5">
                        @php
                            $passengers = $request->passenger_names ?? [];
                            if (is_string($passengers)) {
                                $passengers = json_decode($passengers, true) ?? [];
                            }
                        @endphp
                        @if(count($passengers) > 0)
                            <div class="grid grid-cols-2 gap-1.5 text-xs font-semibold text-black">
                                @foreach($passengers as $idx => $p)
                                    <div class="border-b border-gray-300 pb-0.5">{{ $idx + 1 }}. {{ $p['name'] ?? $p }}</div>
                                @endforeach
                            </div>
                        @else
                            <span class="text-xs text-black font-light italic">No passengers specified.</span>
                        @endif

                        @if($request->has_other_passengers && $request->other_passengers)
                            <div class="mt-1.5 pt-1 border-t border-gray-300 text-xs text-black">
                                <span class="font-bold uppercase text-[10px] text-gray-700">Others / Students:</span>
                                <span class="font-semibold text-black ml-1">{{ $request->other_passengers }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Footer approvals section (2 Columns) -->
            <div class="grid grid-cols-2 gap-8 mt-6 mb-4">
                <!-- Left: Client's Signature and Received by -->
                <div class="flex flex-col justify-between pr-4">
                    <!-- Client's Signature -->
                    <div class="flex flex-col items-center">
                        @if(!empty($request->signature_url))
                            <img src="{{ $request->signature_url }}" alt="Client Signature" class="h-11 max-h-11 object-contain mb-1" />
                        @else
                            <div class="h-11"></div>
                        @endif
                        <div class="w-full border-t border-black text-center pt-1">
                            <span class="text-xs font-bold text-black uppercase">Client's Signature</span>
                        </div>
                    </div>

                    <!-- Received by -->
                    <div class="flex flex-col mt-8">
                        <span class="text-[10px] font-bold text-black uppercase">Received by:</span>
                        <div class="w-full border-b border-black mt-6"></div>
                    </div>
                </div>

                <!-- Right: Approved / Disapproved checkboxes and Joel Tumamao signature -->
                <div class="flex flex-col justify-between pl-6">
                    <!-- Checkboxes -->
                    @php
                        $isApproved = in_array($request->status, ['approved', 'completed', 'on_trip']);
                        $isDisapproved = $request->status === 'rejected';
                    @endphp
                    <div class="flex items-center gap-5 text-xs font-bold text-black select-none pointer-events-none min-h-[44px]">
                        <div class="flex items-center gap-1.5 flex-shrink-0">
                            <div class="w-4 h-4 rounded flex items-center justify-center border border-black {{ $isApproved ? 'bg-black text-white' : 'bg-white' }}" style="border-width: 1.5px;">
                                @if($isApproved)
                                    <svg class="w-3 h-3 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                @endif
                            </div>
                            <span>Approved</span>
                        </div>

                        <div class="flex items-center gap-1.5 flex-shrink-0">
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

                    @php
                        $gsoName = \App\Models\CampusSetting::get('gso_name', 'JOEL A. TUMAMAO');
                        $gsoTitle = \App\Models\CampusSetting::get('gso_title', 'General Services Officer');
                    @endphp
                    <!-- GSO signature (Line is on top of GSO Officer) -->
                    <div class="flex flex-col items-center mt-8">
                        <div class="h-6"></div>
                        <div class="w-full border-t border-black text-center pt-1">
                            <span class="text-sm font-extrabold text-black uppercase tracking-wide">{{ $gsoName }}</span>
                        </div>
                        <span class="text-[10px] text-black uppercase font-semibold">{{ $gsoTitle }}</span>
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

            const sourceDoc = document.querySelector('.request-form-doc');
            const filename = 'Vehicle-Request-Form-{{ $request->request_number }}.pdf';

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
            clone.style.padding = '32px 36px';
            clone.style.boxShadow = 'none';
            clone.style.border = 'none';
            clone.style.backgroundColor = '#ffffff';

            offscreen.appendChild(clone);
            document.body.appendChild(offscreen);

            const opt = {
                margin:       [0.2, 0.2, 0.2, 0.2], // top, left, bottom, right in inches
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
