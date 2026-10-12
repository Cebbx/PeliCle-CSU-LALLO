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
                margin: 0.4in;
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
            <button onclick="if (window.history.length > 1) { window.history.back(); } else { window.close(); }" class="flex-1 sm:flex-initial bg-gray-100 hover:bg-gray-200 active:scale-95 text-gray-700 font-bold py-2.5 px-3.5 sm:px-4 rounded-lg transition-all flex items-center justify-center gap-2 text-xs sm:text-sm shadow-sm border border-gray-300 cursor-pointer select-none">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Back</span>
            </button>
            <button id="btnDownloadPdf" onclick="downloadPDF(this)" class="flex-1 sm:flex-initial bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold py-2.5 px-3.5 sm:px-4 rounded-lg transition-all flex items-center justify-center gap-2 text-xs sm:text-sm shadow-md cursor-pointer select-none">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Download PDF File</span>
            </button>
            <button id="btnPrintDoc" onclick="triggerPrint(this)" class="flex-1 sm:flex-initial bg-sky-600 hover:bg-sky-700 active:scale-95 text-white font-bold py-2.5 px-3.5 sm:px-4 rounded-lg transition-all flex items-center justify-center gap-2 text-xs sm:text-sm shadow-md cursor-pointer select-none">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Print Document</span>
            </button>
        </div>
    </div>

    <!-- Main Form Page Sheet -->
    <div class="bg-white w-full max-w-[8.5in] mx-auto p-4 sm:p-8 shadow-lg border border-gray-200 flex flex-col print-container" style="min-height: 10.5in;">
        <!-- Header -->
        <div class="w-full border-b-2 border-black pb-4 mb-6 flex justify-center">
            <div class="relative flex flex-col items-center">
                <img src="{{ asset('csu-logo.png') }}" alt="CSU Logo" crossorigin="anonymous" class="absolute right-[100%] mr-6 top-1/2 -translate-y-1/2 w-16 h-16 object-contain" />
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
                <div class="col-span-8 p-3 border-r border-black flex flex-col justify-between" style="min-height: 70px;">
                    <span class="text-[10px] uppercase font-bold text-black">Client:</span>
                    <span class="text-sm font-bold text-black text-center mt-1 border-b border-gray-300 w-11/12 mx-auto">{{ $request->employee_name }}</span>
                    <span class="text-[9px] text-black text-center mt-0.5">Name</span>
                </div>
                <div class="col-span-4 p-3 flex flex-col justify-between" style="min-height: 70px;">
                    <span class="text-[10px] uppercase font-bold text-black">Date:</span>
                    <span class="text-sm font-bold text-black text-center mt-1 border-b border-gray-300 w-11/12 mx-auto">{{ \Carbon\Carbon::parse($request->created_at)->format('M d, Y') }}</span>
                    <span class="text-[9px] text-black text-center mt-0.5">Office: <span class="font-bold text-black">{{ $request->department }}</span></span>
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
                    @if(!empty($request->signature_url))
                        <img src="{{ $request->signature_url }}" alt="Client Signature" class="h-10 max-h-10 object-contain mb-1" />
                    @else
                        <div class="h-10"></div>
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

    <!-- Mobile-Optimized PDF Download & Print Scripts -->
    <script>
        async function downloadPDF(btn) {
            const btnEl = btn || document.querySelector('#btnDownloadPdf');
            const originalHtml = btnEl ? btnEl.innerHTML : '';

            if (typeof html2pdf === 'undefined') {
                alert('PDF engine is still loading. Please wait a few seconds or use "Print Document" to Save as PDF directly!');
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

            const opt = {
                margin:       0.35,
                filename:     filename,
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  {
                    scale: 2,
                    useCORS: true,
                    allowTaint: true,
                    logging: false,
                    scrollX: 0,
                    scrollY: 0,
                    windowWidth: 850,
                    onclone: function(clonedDoc) {
                        const el = clonedDoc.querySelector('.print-container');
                        if (el) {
                            el.style.width = '8.5in';
                            el.style.maxWidth = '8.5in';
                            el.style.minWidth = '8.5in';
                            el.style.margin = '0 auto';
                        }
                    }
                },
                jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
            };

            try {
                const pdfWorker = html2pdf().set(opt).from(element);
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
                alert('Could not download PDF directly on this device. Opening print preview where you can select "Save as PDF"!');
                window.print();
                if (btnEl) {
                    btnEl.disabled = false;
                    btnEl.innerHTML = originalHtml;
                }
            }
        }

        function triggerPrint(btn) {
            if (btn) {
                btn.classList.add('opacity-75');
                setTimeout(() => btn.classList.remove('opacity-75'), 1200);
            }
            window.print();
        }
    </script>

</body>
</html>
