<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel Order - {{ $ticket->ticket_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        body {
            font-family: 'Arial', 'Calibri', sans-serif;
            background-color: #f3f4f6;
            color: black;
        }
        @media print {
            html, body {
                display: block !important;
                background: white !important;
                color: black !important;
                font-size: 12px;
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
                min-height: auto !important;
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
                size: letter;
                margin: 0.5in;
            }
        }
    </style>
</head>
@php
    $backUrl = url()->previous();
    if ($backUrl === url()->current() || empty($backUrl)) {
        $backUrl = url('/admin/trip-tickets');
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
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ $backUrl }}" onclick="handleBack(event)" class="bg-gray-100 hover:bg-gray-200 active:bg-gray-300 text-gray-700 font-bold py-1.5 px-3 rounded-lg transition-colors flex items-center gap-1.5 text-xs border border-gray-300 shadow-sm cursor-pointer mr-1" title="Go Back">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                <span>Back</span>
            </a>
            <span class="text-gray-800 font-bold text-xs sm:text-sm mr-1">Travel Order</span>
            <!-- Interactive Purpose Selection Pills -->
            <div class="flex items-center gap-1.5 bg-gray-100 p-1 rounded-lg border border-gray-300">
                <span class="text-xs font-semibold text-gray-500 pl-1.5 mr-1">Purpose:</span>
                <button type="button" id="btnOB" onclick="togglePurposeType('business')" class="{{ $isOB ? 'bg-blue-600 text-white font-bold shadow-sm' : 'bg-white text-gray-700 hover:bg-gray-200' }} py-1 px-2 rounded text-xs transition-colors flex items-center gap-1">
                    <span>✓</span> Official Business
                </button>
                <button type="button" id="btnOT" onclick="togglePurposeType('time')" class="{{ !$isOB ? 'bg-blue-600 text-white font-bold shadow-sm' : 'bg-white text-gray-700 hover:bg-gray-200' }} py-1 px-2 rounded text-xs transition-colors flex items-center gap-1">
                    <span>✓</span> Official Time
                </button>
            </div>
            <!-- Transportation Allowed toggle pill -->
            <button type="button" id="btnTA" onclick="toggleTransAllowed()" class="bg-emerald-600 text-white font-bold shadow-sm py-1.5 px-2.5 rounded-lg text-xs transition-colors flex items-center gap-1 border border-emerald-700">
                <span id="btnTAIcon">✓</span> Transportation Allowed
            </button>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto">
            <a href="{{ route('trip-tickets.print', $ticket->id) }}" target="_blank" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-3 rounded-lg transition-colors flex items-center justify-center gap-1.5 text-xs shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Trip Ticket
            </a>
            <button id="downloadPdfBtn" type="button" onclick="downloadPDF()" class="flex-1 sm:flex-initial bg-green-600 hover:bg-green-700 active:bg-green-800 text-white font-bold py-2 px-3 rounded-lg transition-colors flex items-center justify-center gap-1.5 text-xs shadow-sm cursor-pointer disabled:opacity-50">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span id="downloadBtnText">Download PDF</span>
            </button>
            <button id="printDocBtn" type="button" onclick="triggerPrint()" class="flex-1 sm:flex-initial bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white font-bold py-2 px-3 rounded-lg transition-colors flex items-center justify-center gap-1.5 text-xs shadow-sm cursor-pointer">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Print Document</span>
            </button>
        </div>
    </div>

    <!-- Wrapper for smooth mobile horizontal scroll if needed -->
    <div class="overflow-x-auto w-full max-w-[7.5in] mx-auto pb-4">
        <!-- Main Travel Order Sheet (Matches Official CSU Photo Exactly) -->
        <div class="bg-white w-full max-w-[7.5in] mx-auto p-8 sm:p-10 flex flex-col justify-between print-container relative shadow-lg border border-gray-200 text-black text-[13px] leading-relaxed" style="min-height: 10.2in; min-width: 680px;">
        
        <div>
            <!-- Header (matching official CSU layout) -->
            <div class="w-full mb-6 relative">
                <img src="/csu-logo.png" alt="CSU Logo" class="absolute left-4 top-1 w-16 h-16 object-contain" />
                <div class="text-center">
                    <p class="text-[12px] text-black">Republic of the Philippines</p>
                    <h1 class="text-[15px] font-bold uppercase text-black tracking-wide">CAGAYAN STATE UNIVERSITY</h1>
                    <p class="text-[12px] text-black">Lal-lo, Cagayan</p>
                    <h2 class="text-[14px] font-bold uppercase text-black tracking-wider mt-1">TRAVEL ORDER</h2>
                    <div class="text-[12px] text-black mt-0.5 flex items-center justify-center gap-1">
                        <span>Series of {{ $seriesYear }}</span>
                        <input type="text" value="{{ $seriesMonth }}" class="border-b border-black text-center w-8 bg-transparent outline-none font-bold" title="Series Month" />
                        <span>-</span>
                        <input type="text" value="{{ $seriesDay }}" class="border-b border-black text-center w-8 bg-transparent outline-none font-bold" title="Series Day of Request" />
                    </div>
                </div>
            </div>

            <!-- Form Fields (Interactive & Editable) -->
            <div class="space-y-3 mt-4 text-[13px]">
                
                <!-- Name with 3 underlined lines matching physical form -->
                <div>
                    <div class="flex items-end">
                        <span class="w-16 font-medium">Name:</span>
                        <input type="text" value="{{ $name }}" class="flex-grow border-b border-black outline-none px-2 font-bold h-6 bg-transparent" placeholder="Enter Full Name" />
                    </div>
                    <div class="border-b border-black w-full h-5 ml-0"></div>
                    <div class="border-b border-black w-full h-5 ml-0"></div>
                </div>

                <!-- Position -->
                <div class="flex items-end pt-1">
                    <span class="w-16 font-medium">Position:</span>
                    <input type="text" value="{{ $position }}" class="flex-grow border-b border-black outline-none px-2 h-6 bg-transparent" placeholder="Enter Position" />
                </div>

                <!-- Departure & Arrival -->
                <div class="flex items-end gap-4">
                    <span class="w-20 font-medium">Departure:</span>
                    <input type="text" value="{{ $departure }}" class="flex-grow border-b border-black outline-none px-2 h-6 bg-transparent" placeholder="Departure Date/Time" />
                    <span class="font-medium">Arrival:</span>
                    <input type="text" value="{{ $arrival }}" class="w-48 border-b border-black outline-none px-2 h-6 bg-transparent" placeholder="Arrival Date/Time" />
                </div>

                <!-- Station & Destination -->
                <div class="flex items-end gap-4">
                    <span class="w-16 font-medium">Station:</span>
                    <input type="text" value="CSU Lal-lo Campus" class="flex-grow border-b border-black outline-none px-2 h-6 bg-transparent" />
                    <span class="font-medium">Destination:</span>
                    <input type="text" value="{{ $destination }}" class="flex-grow border-b border-black outline-none px-2 h-6 bg-transparent font-bold" placeholder="Destination" />
                </div>

                <!-- Purpose Section with 2 ruled lines matching physical form -->
                <div class="pt-1.5">
                    <div class="flex items-start">
                        <span class="w-16 font-medium whitespace-nowrap" style="line-height: 28px;">Purpose:</span>
                        <div id="purposeField" contenteditable="true" class="flex-grow outline-none text-[13px] font-normal" style="min-height: 56px; line-height: 28px; background-image: repeating-linear-gradient(transparent, transparent 27px, black 27px, black 28px); background-attachment: local; padding-left: 4px; padding-right: 4px;" title="Click to edit Purpose">{{ $purpose ?: 'Official Business' }}</div>
                    </div>
                </div>

                <!-- Official Business / Official Time + Transportation Allowed (Checkmark lines) -->
                <div class="flex flex-wrap items-center justify-between gap-4 pt-3">
                    <div class="flex items-center gap-6">
                        <!-- Official Business Check -->
                        <div class="flex items-center cursor-pointer select-none group" onclick="togglePurposeType('business')" title="Click to select Official Business">
                            <span id="lineOB" class="border-b-2 border-black font-bold text-center inline-block w-10 h-5 pb-0.5 mr-2 text-[13px] group-hover:bg-blue-50 transition-colors">{{ $isOB ? '✓' : '' }}</span>
                            <span class="font-medium text-[12px] group-hover:text-blue-700">Official Business</span>
                        </div>
                        <!-- Official Time Check -->
                        <div class="flex items-center cursor-pointer select-none group" onclick="togglePurposeType('time')" title="Click to select Official Time">
                            <span id="lineOT" class="border-b-2 border-black font-bold text-center inline-block w-10 h-5 pb-0.5 mr-2 text-[13px] group-hover:bg-blue-50 transition-colors">{{ !$isOB ? '✓' : '' }}</span>
                            <span class="font-medium text-[12px] group-hover:text-blue-700">Official Time</span>
                        </div>
                    </div>

                    <!-- Transportation Allowed (Checkable with line) -->
                    <div class="flex items-center flex-grow min-w-[260px]">
                        <div class="flex items-center cursor-pointer select-none group mr-1.5" onclick="toggleTransAllowed()" title="Click to check / uncheck Transportation Allowed">
                            <span id="lineTA" class="border-b-2 border-black font-bold text-center inline-block w-10 h-5 pb-0.5 mr-2 text-[13px] group-hover:bg-emerald-50 transition-colors">✓</span>
                            <span class="font-medium whitespace-nowrap text-[12px] group-hover:text-emerald-700">Transportation Allowed:</span>
                        </div>
                        <input type="text" value="{{ $vehicleName ?: 'CSU Vehicle' }}" class="flex-grow border-b border-black outline-none px-2 h-5 bg-transparent font-medium text-[12px]" placeholder="Transportation details" />
                    </div>
                </div>

                <!-- Travel Charged Against & Remarks (Fully Editable with proper underlines) -->
                <div class="flex flex-wrap items-center justify-between gap-6 pt-2">
                    <div class="flex items-end flex-grow min-w-[240px]">
                        <span class="font-medium whitespace-nowrap text-[12px] mr-2">Travel Charged Against:</span>
                        <input type="text" value="" class="flex-grow border-b border-black outline-none px-2 h-6 bg-transparent text-[12px] font-normal" placeholder="" />
                    </div>
                    <div class="flex items-end flex-grow min-w-[200px]">
                        <span class="font-medium whitespace-nowrap text-[12px] mr-2">Remarks:</span>
                        <input type="text" value="" class="flex-grow border-b border-black outline-none px-2 h-6 bg-transparent text-[12px] font-normal" placeholder="Enter remarks" />
                    </div>
                </div>

            </div>

            <!-- Approvals Section (Vertically stacked matching photo) -->
            <div class="mt-8 space-y-5 text-[13px] text-black">
                
                <!-- Recommending Approval -->
                <div class="w-full">
                    <p class="font-medium mb-3">Recommending Approval:</p>
                    <div class="text-right pr-4 sm:pr-8">
                        <input type="text" value="JOEL A. TUMAMAO/GSO" class="font-bold text-[13px] text-right uppercase bg-transparent outline-none border-b border-transparent hover:border-gray-300 w-72" />
                        <p class="text-[12px] text-gray-800 font-normal">Immediate Supervisor</p>
                    </div>
                </div>

                <!-- Approved -->
                <div class="w-full">
                    <p class="font-medium mb-3">Approved:</p>
                    <div class="text-right pr-4 sm:pr-8">
                        <input type="text" value="JAMES B. CABILDO, PHD, ASEAN Engr." class="font-bold text-[13px] text-right uppercase bg-transparent outline-none border-b border-transparent hover:border-gray-300 w-80" />
                        <p class="text-[12px] text-gray-800 font-normal">Campus Executive Officer</p>
                    </div>
                </div>

            </div>

            <!-- Appearance Certified Section (Matching official layout without enclosing border box) -->
            <div class="mt-8 text-black">
                <p class="font-bold uppercase tracking-wider text-[12px] mb-2">APPEARANCE CERTIFIED:</p>
                <div class="grid grid-cols-2 gap-10 text-[12px]">
                    <div>
                        <p class="text-center font-medium mb-1">Date</p>
                        <div class="space-y-4">
                            <div class="border-b border-black h-5"></div>
                            <div class="border-b border-black h-5"></div>
                            <div class="border-b border-black h-5"></div>
                        </div>
                    </div>
                    <div>
                        <p class="text-center font-medium mb-1">Name & Signature</p>
                        <div class="space-y-4">
                            <div class="border-b border-black h-5"></div>
                            <div class="border-b border-black h-5"></div>
                            <div class="border-b border-black h-5"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Document Footer Code and Revision (Bottom of Letter page) -->
        <div class="pt-6 flex justify-between items-center text-[10px] text-black font-sans">
            <div class="flex items-center gap-12">
                <span>4</span>
                <span class="tracking-wide">F-OCEO-60105</span>
            </div>
            <span>Rev. 01, 01-03-2024</span>
        </div>
    </div>

    <!-- Interactive Scripts & PDF Download -->
    <script>
        function togglePurposeType(type) {
            const obSpan = document.getElementById('lineOB');
            const otSpan = document.getElementById('lineOT');
            const btnOB = document.getElementById('btnOB');
            const btnOT = document.getElementById('btnOT');

            if (type === 'business') {
                obSpan.innerText = '✓';
                otSpan.innerText = '';
                if (btnOB && btnOT) {
                    btnOB.className = 'bg-blue-600 text-white font-bold shadow-sm py-1 px-2.5 rounded text-xs transition-colors flex items-center gap-1';
                    btnOT.className = 'bg-white text-gray-700 hover:bg-gray-200 py-1 px-2.5 rounded text-xs transition-colors flex items-center gap-1';
                }
            } else {
                obSpan.innerText = '';
                otSpan.innerText = '✓';
                if (btnOB && btnOT) {
                    btnOB.className = 'bg-white text-gray-700 hover:bg-gray-200 py-1 px-2.5 rounded text-xs transition-colors flex items-center gap-1';
                    btnOT.className = 'bg-blue-600 text-white font-bold shadow-sm py-1 px-2.5 rounded text-xs transition-colors flex items-center gap-1';
                }
            }
        }

        let isTransAllowed = true;
        function toggleTransAllowed() {
            isTransAllowed = !isTransAllowed;
            const taSpan = document.getElementById('lineTA');
            const btnTA = document.getElementById('btnTA');
            const btnIcon = document.getElementById('btnTAIcon');

            if (isTransAllowed) {
                if (taSpan) taSpan.innerText = '✓';
                if (btnIcon) btnIcon.innerText = '✓';
                if (btnTA) {
                    btnTA.className = 'bg-emerald-600 text-white font-bold shadow-sm py-1.5 px-3 rounded-lg text-xs transition-colors flex items-center gap-1.5 ml-1 border border-emerald-700';
                }
            } else {
                if (taSpan) taSpan.innerText = '';
                if (btnIcon) btnIcon.innerText = '';
                if (btnTA) {
                    btnTA.className = 'bg-white text-gray-700 hover:bg-gray-100 py-1.5 px-3 rounded-lg text-xs transition-colors flex items-center gap-1.5 ml-1 border border-gray-300';
                }
            }
        }

        // Sync inputs so html2pdf captures user typed values
        document.querySelectorAll('input, textarea').forEach(el => {
            el.addEventListener('input', () => {
                if (el.tagName === 'TEXTAREA') {
                    el.innerHTML = el.value;
                } else {
                    el.setAttribute('value', el.value);
                }
            });
        });

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
                filename:     'Travel-Order-{{ $ticket->ticket_number }}.pdf',
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
