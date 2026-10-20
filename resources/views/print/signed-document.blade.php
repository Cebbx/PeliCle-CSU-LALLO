<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <script src="{{ asset('js/tailwindcss.js') }}"></script>
    <style>
        body {
            font-family: 'Arial', 'Calibri', sans-serif;
            background-color: #f3f4f6;
            color: black;
        }
        @media print {
            body {
                background: white !important;
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
            img.signed-doc-img {
                max-width: 100% !important;
                max-height: 98vh !important;
                object-fit: contain !important;
                display: block !important;
                margin: 0 auto !important;
            }
            @page {
                size: letter portrait;
                margin: 0.3in;
            }
        }
    </style>
</head>
<body class="bg-gray-100 py-3 sm:py-6 px-2 sm:px-4 min-h-screen flex flex-col">

    <!-- Floating Top Bar (Sticky, Mobile Responsive, hidden on print) -->
    <div class="no-print sticky top-2 z-50 max-w-[8.5in] w-full mx-auto mb-4 bg-white/95 backdrop-blur-sm p-3 sm:p-4 rounded-xl shadow-lg border border-gray-200 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2 sm:gap-3">
            <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600 flex-shrink-0">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <div>
                <h1 class="text-gray-900 font-extrabold text-xs sm:text-base leading-tight">{{ $title }}</h1>
                <p class="text-[10px] sm:text-xs text-gray-500 mt-0.5">{{ $subtitle }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
            <!-- Back Button -->
            <button onclick="if (window.history.length > 1) { window.history.back(); } else { window.close(); setTimeout(() => { window.location.href = '{{ url()->previous() ?: route('dashboard') }}'; }, 200); }" class="flex-1 sm:flex-initial bg-slate-100 hover:bg-slate-200 active:scale-95 text-slate-700 font-bold py-2.5 px-3.5 sm:px-4 rounded-lg transition-all flex items-center justify-center gap-2 text-xs sm:text-sm shadow-sm border border-slate-300 cursor-pointer select-none">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Back</span>
            </button>

            <!-- Download Button -->
            <button onclick="downloadSignedDoc(this, '{{ $fileUrl }}', '{{ $downloadFilename }}')" class="flex-1 sm:flex-initial bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold py-2.5 px-3.5 sm:px-4 rounded-lg transition-all flex items-center justify-center gap-2 text-xs sm:text-sm shadow-md cursor-pointer select-none">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Download {{ strtoupper($extension) }} File</span>
            </button>

            <!-- Print Button -->
            <button onclick="triggerPrint(this)" class="flex-1 sm:flex-initial bg-sky-600 hover:bg-sky-700 active:scale-95 text-white font-bold py-2.5 px-3.5 sm:px-4 rounded-lg transition-all flex items-center justify-center gap-2 text-xs sm:text-sm shadow-md cursor-pointer select-none">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Print Document</span>
            </button>

            @php
                $currentUser = auth('admin')->user() ?? auth()->user();
                $isAdminOrGso = auth('admin')->check() || ($currentUser && in_array(strtolower($currentUser->role ?? ''), ['admin', 'gso', 'staff', 'motorpool']));
                
                $isVerified = !empty($record->document_verified_at);
                $driverName = $record instanceof \App\Models\TripTicket 
                    ? ($record->driver?->name ?? 'Assigned Driver') 
                    : ($record->tripTicket?->driver?->name ?? 'Assigned Driver');
                $driverPhone = $record instanceof \App\Models\TripTicket 
                    ? ($record->driver?->contact_number ?? '') 
                    : ($record->tripTicket?->driver?->contact_number ?? '');
                $verifyRoute = $type === 'trip-ticket' 
                    ? route('trip-tickets.verify-document-and-send-sms', $record->id)
                    : route('vehicle-requests.verify-document-and-send-sms', $record->id);
            @endphp

            @if($isAdminOrGso)
                <!-- Verify Document & Send SMS to Driver Button (Admin / GSO) -->
                <button id="verifySmsBtn" 
                        type="button" 
                        data-verified="{{ $isVerified ? '1' : '0' }}" 
                        onclick="verifyAndSendSms(this, '{{ $verifyRoute }}', '{{ addslashes($driverName) }}', '{{ addslashes($driverPhone) }}')" 
                        class="flex-1 sm:flex-initial {{ $isVerified ? 'bg-emerald-700 hover:bg-emerald-800' : 'bg-gradient-to-r from-emerald-600 via-teal-600 to-sky-600 hover:from-emerald-700 hover:to-sky-700 ring-2 ring-emerald-300' }} active:scale-95 text-white font-bold py-2.5 px-3.5 sm:px-4 rounded-lg transition-all flex items-center justify-center gap-2 text-xs sm:text-sm shadow-md cursor-pointer select-none">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        @if($isVerified)
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        @else
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                        @endif
                    </svg>
                    <span>{{ $isVerified ? 'Verified (Resend SMS)' : 'Verify & Send SMS to Driver' }}</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Status Banner Alert Box -->
    <div id="statusAlertBox" class="no-print max-w-[8.5in] w-full mx-auto mb-3 p-3 rounded-xl border flex items-center justify-between text-xs sm:text-sm font-semibold shadow-sm {{ $isVerified ? 'bg-emerald-50 border-emerald-300 text-emerald-900' : 'bg-amber-50 border-amber-300 text-amber-900' }}">
        <div class="flex items-center gap-2">
            <span class="text-base">{{ $isVerified ? '✅' : '⏳' }}</span>
            <div id="statusAlertText">
                @if($isVerified)
                    <strong>Verified CEO Document:</strong> This document has been verified by Admin/GSO and SMS dispatch has been sent to Driver ({{ $driverName }}). Trip Status: <strong>{{ ($record->status === 'on_trip' || $record->status === 'active') ? 'On Trip' : 'Ready (Waiting)' }}</strong>.
                @else
                    <strong>Awaiting Admin Verification:</strong> The CEO-signed document has been uploaded. Click <strong>"Verify & Send SMS to Driver"</strong> above to officially verify the document, dispatch the driver via SMS, and update the trip status.
                @endif
            </div>
        </div>
        <span class="text-[11px] uppercase tracking-wider px-2.5 py-0.5 rounded-full {{ $isVerified ? 'bg-emerald-200 text-emerald-800' : 'bg-amber-200 text-amber-800' }}">
            {{ $isVerified ? (($record->status === 'on_trip' || $record->status === 'active') ? 'On Trip' : 'Ready (Waiting)') : 'For Admin Review' }}
        </span>
    </div>

    <!-- Document Viewer Container -->
    <div class="max-w-[8.5in] w-full mx-auto flex-1 flex flex-col print-container">
        @if($extension === 'pdf')
            <!-- PDF Viewer -->
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden flex-1 min-h-[85vh]">
                <iframe id="pdfFrame" src="{{ $fileUrl }}#toolbar=1&navpanes=0" class="w-full h-[85vh] border-0" title="PDF Document"></iframe>
            </div>
        @elseif(in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif']))
            <!-- Image Viewer -->
            <div class="bg-white rounded-xl shadow-md border border-gray-200 p-4 sm:p-6 flex items-center justify-center">
                <img src="{{ $fileUrl }}" alt="Signed Document" class="signed-doc-img max-w-full h-auto object-contain rounded-lg shadow-sm border border-gray-100" />
            </div>
        @else
            <!-- Other file types (e.g. DOCX) -->
            <div class="bg-white rounded-xl shadow-md border border-gray-200 p-8 text-center my-auto">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h2 class="text-lg font-bold text-gray-800 mb-1">{{ $fileName }}</h2>
                <p class="text-sm text-gray-500 mb-6">This document format ({{ strtoupper($extension) }}) cannot be directly previewed inline.</p>
                <a href="{{ $fileUrl }}" download="{{ $downloadFilename }}" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-6 rounded-xl shadow transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Download File to View / Print
                </a>
            </div>
        @endif
    </div>

    <!-- Mobile-Optimized Download & Print Scripts -->
    <script>
        async function downloadSignedDoc(btn, url, filename) {
            const originalHtml = btn ? btn.innerHTML : '';
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = `
                    <svg class="animate-spin h-4 w-4 text-white inline-block flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Downloading...</span>
                `;
            }

            try {
                const response = await fetch(url);
                if (!response.ok) throw new Error('Network error');
                const blob = await response.blob();
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

                if (btn) {
                    btn.innerHTML = `<span>✓ Downloaded!</span>`;
                    setTimeout(() => {
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                    }, 2500);
                }
            } catch (err) {
                console.error('Download error:', err);
                // Fallback: direct navigation download
                const link = document.createElement('a');
                link.href = url;
                link.download = filename;
                link.target = '_blank';
                document.body.appendChild(link);
                link.click();
                setTimeout(() => document.body.removeChild(link), 1000);
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            }
        }

        function triggerPrint(btn) {
            if (btn) {
                btn.classList.add('opacity-75');
                setTimeout(() => btn.classList.remove('opacity-75'), 1200);
            }

            @if($extension === 'pdf')
                const frame = document.getElementById('pdfFrame');
                if (frame && frame.contentWindow) {
                    try {
                        frame.contentWindow.focus();
                        frame.contentWindow.print();
                        return;
                    } catch (e) {
                        console.log('Direct iframe print blocked, using fallback window print');
                    }
                }
            @endif
            window.print();
        }

        async function verifyAndSendSms(btn, url, driverName, driverPhone) {
            const isCurrentlyVerified = btn.getAttribute('data-verified') === '1';
            const confirmMsg = isCurrentlyVerified 
                ? `Resend the SMS dispatch notification to Driver ${driverName}${driverPhone ? ' (' + driverPhone + ')' : ''}?`
                : `Confirm and verify this CEO-signed document and dispatch the SMS notification to Driver ${driverName}${driverPhone ? ' (' + driverPhone + ')' : ''}?`;

            if (!confirm(confirmMsg)) return;

            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = `
                <svg class="animate-spin h-4 w-4 text-white inline-block flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Verifying & Sending SMS...</span>
            `;

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                });

                const data = await response.json();
                if (data.success) {
                    btn.setAttribute('data-verified', '1');
                    btn.className = "flex-1 sm:flex-initial bg-emerald-700 hover:bg-emerald-800 active:scale-95 text-white font-bold py-2.5 px-3.5 sm:px-4 rounded-lg transition-all flex items-center justify-center gap-2 text-xs sm:text-sm shadow-md cursor-pointer select-none";
                    btn.innerHTML = `
                        <svg class="w-4 h-4 flex-shrink-0 text-emerald-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Verified (Resend SMS)</span>
                    `;
                    btn.disabled = false;

                    const alertBox = document.getElementById('statusAlertBox');
                    if (alertBox) {
                        alertBox.className = "no-print max-w-[8.5in] w-full mx-auto mb-3 p-3 rounded-xl border flex items-center justify-between text-xs sm:text-sm font-semibold shadow-sm bg-emerald-50 border-emerald-300 text-emerald-900";
                        alertBox.innerHTML = `
                            <div class="flex items-center gap-2">
                                <span class="text-base">✅</span>
                                <div id="statusAlertText">
                                    <strong>Verified CEO Document:</strong> This document has been verified by Admin/GSO and SMS dispatch has been sent to Driver (${driverName}). Trip Status: <strong>${data.status_label}</strong>.
                                </div>
                            </div>
                            <span class="text-[11px] uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-emerald-200 text-emerald-800">
                                ${data.status_label}
                            </span>
                        `;
                    }

                    alert(`✅ ${data.message}`);
                } else {
                    alert('⚠️ ' + (data.message || 'An error occurred.'));
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                }
            } catch (err) {
                console.error(err);
                alert('Network error: Unable to submit verification request.');
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        }
    </script>

</body>
</html>
