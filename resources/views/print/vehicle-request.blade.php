<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Request Form - {{ $request->request_number }}</title>
    <script src="{{ asset('js/tailwindcss.js') }}"></script>
    <script src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <style>
        body {
            font-family: 'Arial', 'Calibri', sans-serif;
            background-color: #f3f4f6; /* Gray background on screen */
            color: black;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .esign-crisp {
            filter: brightness(0) contrast(400%) drop-shadow(0px 0px 0.45px #000000) drop-shadow(0px 0px 0.35px #000000);
            -webkit-filter: brightness(0) contrast(400%) drop-shadow(0px 0px 0.45px #000000) drop-shadow(0px 0px 0.35px #000000);
            image-rendering: -webkit-optimize-contrast;
            image-rendering: crisp-edges;
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
                min-height: 10.3in !important;
                height: 10.3in !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: space-between !important;
                border: none !important;
                box-shadow: none !important;
                padding: 0.15in 0.25in !important;
                margin: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                box-sizing: border-box !important;
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
            <span class="text-gray-800 font-extrabold text-xs sm:text-sm tracking-tight">Vehicle Request Form</span>
            <span class="text-[10px] sm:text-xs text-gray-500 font-mono hidden sm:inline">({{ $request->request_number }})</span>
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

    <!-- Main Form Page Sheet -->
    <div class="bg-white w-full max-w-[8.5in] mx-auto p-3.5 sm:p-7 shadow-md sm:shadow-lg border border-gray-200 flex flex-col justify-between print-container box-border min-h-[9.8in] sm:min-h-[10.2in]">
        <!-- Header & Title Block -->
        <div>
            <!-- Header (Centered Header with Logo beside University text) -->
            <div class="flex items-center justify-center gap-3 sm:gap-4 border-b-2 border-black pb-2.5 sm:pb-3 mb-3 sm:mb-4">
                <img src="{{ asset('csu-logo.png') }}" alt="CSU Logo" crossorigin="anonymous" class="w-12 h-12 sm:w-14 sm:h-14 object-contain flex-shrink-0" onerror="this.src='/csu-logo-sm.png'" />
                <div class="text-center">
                    <h2 class="text-[10px] sm:text-xs uppercase tracking-wider text-black font-medium leading-tight">Republic of the Philippines</h2>
                    <h1 class="text-xs sm:text-base font-extrabold uppercase text-black tracking-wide mt-0.5 leading-tight">Cagayan State University</h1>
                    <h3 class="text-[10px] sm:text-xs text-black mt-0.5 font-medium leading-tight">Lal-lo Campus</h3>
                    <h2 class="text-[9px] sm:text-[11px] font-bold uppercase tracking-wider text-black mt-0.5 leading-tight">General Service Office</h2>
                </div>
            </div>

            <!-- Title -->
            <div class="text-center mb-3 sm:mb-4">
                <h1 class="text-xs sm:text-sm font-extrabold tracking-wide uppercase border-b border-black inline-block pb-0.5">Vehicle Request Form</h1>
            </div>
        </div>

        <!-- Request Details Table Grid -->
        <div class="border border-black mb-3 sm:mb-4 text-xs sm:text-sm">
            <!-- Row 1 -->
            <div class="grid grid-cols-12 border-b border-black">
                <div class="col-span-7 sm:col-span-7 p-2 sm:p-2.5 border-r border-black flex flex-col justify-between" style="min-height: 68px;">
                    <span class="text-[9px] sm:text-[10px] uppercase font-bold text-black">Client:</span>
                    <span class="text-xs sm:text-sm font-bold text-black text-center mt-1 w-11/12 mx-auto">{{ $request->employee_name }}</span>
                    <span class="text-[8px] sm:text-[9px] text-black text-center mt-0.5">Name</span>
                </div>
                <div class="col-span-5 sm:col-span-5 flex flex-col justify-between p-0" style="min-height: 68px;">
                    <!-- Top Sub-Row: Date | Value -->
                    <div class="grid grid-cols-12 border-b border-black">
                        <div class="col-span-4 border-r border-black px-1.5 py-1 sm:py-1.5 flex items-center justify-center">
                            <span class="text-[10px] sm:text-xs font-bold text-black">Date</span>
                        </div>
                        <div class="col-span-8 px-2 py-1 sm:py-1.5 flex items-center justify-center">
                            <span class="text-[11px] sm:text-xs font-bold text-black text-center">{{ \Carbon\Carbon::parse($request->created_at)->format('M d, Y') }}</span>
                        </div>
                    </div>
                    <!-- Bottom Sub-Row: Office Value above line + Office label below line -->
                    <div class="px-2 pt-1.5 pb-1 flex flex-col items-center justify-end flex-1">
                        <span class="text-[9px] sm:text-[10px] font-bold text-black text-center leading-tight w-11/12 mx-auto break-words">{{ $request->department }}</span>
                        <div class="w-4/5 mx-auto border-b border-black mt-0.5"></div>
                        <span class="text-[9px] sm:text-[11px] font-semibold text-black text-center mt-0.5 leading-none">Office</span>
                    </div>
                </div>
            </div>

            <!-- Row 2 -->
            <div class="grid grid-cols-12 border-b border-black">
                <div class="col-span-6 p-2 sm:p-2.5 border-r border-black flex flex-col" style="min-height: 46px;">
                    <span class="text-[9px] sm:text-[10px] uppercase font-bold text-black">Date of Travel:</span>
                    <span class="text-xs sm:text-sm font-bold text-black mt-0.5">{{ \Carbon\Carbon::parse($request->date)->format('F d, Y') }}</span>
                </div>
                <div class="col-span-6 p-2 sm:p-2.5 flex flex-col" style="min-height: 46px;">
                    <span class="text-[9px] sm:text-[10px] uppercase font-bold text-black">Departure Time:</span>
                    <span class="text-xs sm:text-sm font-bold text-black mt-0.5">{{ \Carbon\Carbon::parse($request->time)->format('g:i A') }}</span>
                </div>
            </div>

            <!-- Row 3 -->
            <div class="grid grid-cols-12 border-b border-black">
                <div class="col-span-12 p-2 sm:p-2.5 flex flex-col" style="min-height: 46px;">
                    <span class="text-[9px] sm:text-[10px] uppercase font-bold text-black">Destination:</span>
                    <span class="text-xs sm:text-sm font-bold text-black mt-0.5">{{ $request->destination }}</span>
                </div>
            </div>

            <!-- Row 4 -->
            <div class="grid grid-cols-12 border-b border-black">
                <div class="col-span-12 p-2 sm:p-2.5 flex flex-col" style="min-height: 56px;">
                    <span class="text-[9px] sm:text-[10px] uppercase font-bold text-black">Purpose of Travel:</span>
                    <span class="text-xs sm:text-sm font-medium text-black mt-0.5 leading-relaxed">{{ $request->purpose }}</span>
                </div>
            </div>

            <!-- Row 5 (Passengers) -->
            <div class="grid grid-cols-12">
                <div class="col-span-4 sm:col-span-3 p-2 sm:p-2.5 border-r border-black flex items-center justify-center">
                    <span class="text-[9px] sm:text-[10px] uppercase font-bold text-black text-center leading-tight">Name of Passengers</span>
                </div>
                <div class="col-span-8 sm:col-span-9 p-2 sm:p-2.5 flex flex-col gap-1.5">
                    @php
                        $passengers = $request->passenger_names ?? [];
                        if (is_string($passengers)) {
                            $passengers = json_decode($passengers, true) ?? [];
                        }
                    @endphp
                    @if(count($passengers) > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-1 text-xs font-semibold text-black">
                            @foreach($passengers as $idx => $p)
                                <div class="py-0.5">{{ $idx + 1 }}. {{ $p['name'] ?? $p }}</div>
                            @endforeach
                        </div>
                    @else
                        <span class="text-xs text-black font-light italic">No passengers specified.</span>
                    @endif

                    @if($request->has_other_passengers && $request->other_passengers)
                        <div class="mt-1 text-xs text-black">
                            <span class="font-bold uppercase text-[9px] sm:text-[10px] text-gray-700">Others / Students:</span>
                            <span class="font-semibold text-black ml-1">{{ $request->other_passengers }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Footer approvals section (2 Columns - Mobile Responsive) -->
        <div class="grid grid-cols-2 gap-3 sm:gap-8 mt-3 sm:mt-5 mb-3">
            <!-- Left: Client's Signature (E-Sign above Requester Name) and Received by -->
            <div class="flex flex-col justify-between pr-1.5 sm:pr-4">
                <!-- Client's Signature -->
                <div class="flex flex-col items-center">
                    @if(!empty($request->signature_url))
                        <img id="client-signature-img" src="{{ $request->signature_url }}" crossorigin="anonymous" alt="Client Signature" class="esign-crisp h-14 sm:h-16 max-h-16 w-auto max-w-[240px] object-contain -mb-1.5 z-10 pointer-events-none select-none" />
                    @else
                        <div class="h-12 sm:h-14"></div>
                    @endif
                    <div class="w-full border-t border-black text-center pt-1">
                        <span class="text-xs sm:text-sm font-extrabold text-black uppercase tracking-wide block leading-tight">{{ $request->employee_name }}</span>
                        <span class="text-[9px] sm:text-[10px] font-semibold text-black uppercase block mt-0.5">Client's Signature</span>
                    </div>
                </div>

                <!-- Received by -->
                <div class="flex flex-col mt-5 sm:mt-7">
                    <span class="text-[9px] sm:text-[10px] font-bold text-black uppercase">Received by:</span>
                    <div class="w-full border-b border-black mt-5 sm:mt-6"></div>
                </div>
            </div>

            <!-- Right: Approved / Disapproved checkboxes and Joel Tumamao signature -->
            <div class="flex flex-col justify-between pl-1.5 sm:pl-6">
                <!-- Checkboxes (Compact & flexible on mobile) -->
                @php
                    $isApproved = in_array($request->status, ['approved', 'completed', 'on_trip']);
                    $isDisapproved = $request->status === 'rejected';
                @endphp
                <div class="flex flex-wrap sm:flex-nowrap items-center gap-2 sm:gap-5 text-[10px] sm:text-xs font-bold text-black select-none pointer-events-none min-h-[36px] sm:min-h-[40px]">
                    <div class="flex items-center gap-1.5 flex-shrink-0">
                        <div class="w-3.5 h-3.5 sm:w-4 sm:h-4 rounded flex items-center justify-center border border-black {{ $isApproved ? 'bg-black text-white' : 'bg-white' }}" style="border-width: 1.5px;">
                            @if($isApproved)
                                <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            @endif
                        </div>
                        <span>Approved</span>
                    </div>

                    <div class="flex items-center gap-1.5 flex-shrink-0">
                        <div class="w-3.5 h-3.5 sm:w-4 sm:h-4 rounded flex items-center justify-center border border-black {{ $isDisapproved ? 'bg-black text-white' : 'bg-white' }}" style="border-width: 1.5px;">
                            @if($isDisapproved)
                                <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            @endif
                        </div>
                        <span>Disapproved</span>
                    </div>
                </div>

                <!-- GSO signature (Line is on top of JOEL A. TUMAMAO) -->
                <div class="flex flex-col items-center mt-5 sm:mt-7">
                    <div class="h-6 sm:h-8"></div>
                    <div class="w-full border-t border-black text-center pt-1">
                        <span class="text-xs sm:text-sm font-extrabold text-black uppercase tracking-wide">{{ \App\Models\User::getGsoName() }}</span>
                    </div>
                    <span class="text-[9px] sm:text-[10px] text-black uppercase font-semibold">{{ \App\Models\User::getGsoTitle() }}</span>
                </div>
            </div>
        </div>

        <!-- Document Metadata footer details -->
        <div class="pt-2 sm:pt-2.5 flex justify-between items-center text-[9px] sm:text-[10px] text-black font-mono border-t border-black mt-3 sm:mt-4">
            <span>F - GSO - 61207</span>
            <span>Rev. No. 00, October 23, 2025</span>
        </div>
    </div>

    <!-- Mobile & PC Optimized 1-Page PDF Download & Print Scripts -->
    <script>
        // Automatically auto-crop empty canvas whitespace and thicken/darken signature strokes for crystal-clear printing & PDF export
        (function enhanceClientSignature() {
            const sigImg = document.getElementById('client-signature-img');
            if (!sigImg || !sigImg.src) return;

            const processImage = () => {
                if (sigImg.dataset.enhanced === '1') return;
                const nw = sigImg.naturalWidth;
                const nh = sigImg.naturalHeight;
                if (!nw || !nh) return;

                try {
                    const tempCanvas = document.createElement('canvas');
                    tempCanvas.width = nw;
                    tempCanvas.height = nh;
                    const tempCtx = tempCanvas.getContext('2d');
                    tempCtx.drawImage(sigImg, 0, 0);

                    const imgData = tempCtx.getImageData(0, 0, nw, nh);
                    const data = imgData.data;

                    // Detect if image has a solid white/light background (uploaded photo) vs transparent canvas
                    let cornerOpaqueCount = 0;
                    const corners = [0, (nw - 1) * 4, (nh - 1) * nw * 4, ((nh - 1) * nw + (nw - 1)) * 4];
                    for (const idx of corners) {
                        if (data[idx + 3] > 200 && data[idx] > 200 && data[idx + 1] > 200 && data[idx + 2] > 200) {
                            cornerOpaqueCount++;
                        }
                    }
                    const isOpaqueBg = cornerOpaqueCount >= 3;

                    let minX = nw, minY = nh, maxX = 0, maxY = 0, found = false;

                    for (let y = 0; y < nh; y++) {
                        for (let x = 0; x < nw; x++) {
                            const i = (y * nw + x) * 4;
                            const r = data[i], g = data[i + 1], b = data[i + 2], a = data[i + 3];
                            const lum = 0.299 * r + 0.587 * g + 0.114 * b;

                            let isInk = false;
                            if (isOpaqueBg) {
                                // Dark ink on light paper
                                if (a > 40 && lum < 200) {
                                    isInk = true;
                                    const strength = Math.min(255, Math.round((220 - lum) * 2.5));
                                    data[i] = 0; data[i + 1] = 0; data[i + 2] = 0; data[i + 3] = strength;
                                } else {
                                    data[i + 3] = 0;
                                }
                            } else {
                                // Transparent canvas signature
                                if (a > 18) {
                                    isInk = true;
                                    data[i] = 0; data[i + 1] = 0; data[i + 2] = 0;
                                    data[i + 3] = Math.min(255, Math.round(a * 2.8));
                                } else {
                                    data[i + 3] = 0;
                                }
                            }

                            if (isInk) {
                                found = true;
                                if (x < minX) minX = x;
                                if (x > maxX) maxX = x;
                                if (y < minY) minY = y;
                                if (y > maxY) maxY = y;
                            }
                        }
                    }

                    if (!found) return;
                    tempCtx.putImageData(imgData, 0, 0);

                    const pad = Math.max(8, Math.round(Math.max(maxX - minX, maxY - minY) * 0.04));
                    const cropX = Math.max(0, minX - pad);
                    const cropY = Math.max(0, minY - pad);
                    const cropW = Math.min(nw - cropX, (maxX - minX) + pad * 2);
                    const cropH = Math.min(nh - cropY, (maxY - minY) + pad * 2);

                    // Target high-resolution output height (240px) so strokes are uniformly bold and sharp
                    const targetH = 240;
                    const scale = targetH / Math.max(cropH, 1);
                    const targetW = Math.max(1, Math.min(900, Math.round(cropW * scale)));

                    const outCanvas = document.createElement('canvas');
                    outCanvas.width = targetW;
                    outCanvas.height = targetH;
                    const outCtx = outCanvas.getContext('2d');
                    outCtx.imageSmoothingEnabled = true;
                    outCtx.imageSmoothingQuality = 'high';

                    // Multi-pass stroke thickening so thin high-DPI lines become bold, crisp ink on print
                    const offsets = [
                        [0, 0], [-0.85, 0], [0.85, 0], [0, -0.85], [0, 0.85],
                        [-0.6, -0.6], [0.6, -0.6], [-0.6, 0.6], [0.6, 0.6]
                    ];
                    for (const [dx, dy] of offsets) {
                        outCtx.drawImage(tempCanvas, cropX, cropY, cropW, cropH, dx, dy, targetW, targetH);
                    }

                    sigImg.dataset.enhanced = '1';
                    sigImg.classList.remove('esign-crisp');
                    sigImg.src = outCanvas.toDataURL('image/png');
                } catch (e) {
                    // Keep CSS .esign-crisp filter fallback if canvas is tainted
                }
            };

            if (sigImg.complete && sigImg.naturalWidth > 0) {
                processImage();
            } else {
                sigImg.addEventListener('load', processImage, { once: true });
            }
        })();

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
            const filename = 'Vehicle-Request-Form-{{ $request->request_number }}.pdf';

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
            clone.style.height = '1010px';
            clone.style.minHeight = '1010px';
            clone.style.margin = '0 auto';
            clone.style.padding = '24px 36px 20px 36px';
            clone.style.boxShadow = 'none';
            clone.style.border = 'none';
            clone.style.backgroundColor = '#ffffff';
            clone.style.boxSizing = 'border-box';
            clone.style.display = 'flex';
            clone.style.flexDirection = 'column';
            clone.style.justifyContent = 'space-between';

            offscreen.appendChild(clone);
            document.body.appendChild(offscreen);

            // 1-Page Guarantee: Letter paper height at 816px width is 1056px.
            // Ensure clone height stays strictly <= 1040px so html2pdf never creates a 2nd page.
            if (clone.offsetHeight > 1040) {
                const ratio = 1030 / clone.offsetHeight;
                clone.style.transform = `scale(${ratio})`;
                clone.style.transformOrigin = 'top center';
                offscreen.style.height = '1035px';
                offscreen.style.overflow = 'hidden';
            }

            const opt = {
                margin:       0,
                filename:     filename,
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  {
                    scale: 2,
                    useCORS: true,
                    allowTaint: false,
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

                // Hard guarantee: strip any extra spill page if ever created
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
