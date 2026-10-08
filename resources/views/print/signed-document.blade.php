<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
                padding: 0 !important;
                margin: 0 !important;
                height: auto !important;
                min-height: 0 !important;
                width: 100% !important;
            }
            .no-print {
                display: none !important;
            }
            .print-container {
                display: block !important;
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
                page-break-inside: avoid !important;
            }
            @page {
                size: letter portrait;
                margin: 0.3in;
            }
        }
    </style>
</head>
@php
    $backUrl = url()->previous();
    if ($backUrl === url()->current() || empty($backUrl)) {
        if ($type === 'vehicle-request') {
            $backUrl = auth('employee')->check() ? url('/employee/vehicle-requests') : url('/admin/vehicle-requests');
        } else {
            $backUrl = auth('driver')->check() ? url('/driver/trip-tickets') : url('/admin/trip-tickets');
        }
    }
@endphp
<body class="bg-gray-100 py-4 sm:py-6 px-2 sm:px-4 min-h-screen flex flex-col">

    <!-- In-App Browser (Messenger / IG) Notification Banner -->
    <div id="inAppNotice" class="no-print hidden max-w-[8.5in] w-full mx-auto mb-3 bg-amber-50 border border-amber-300 rounded-xl p-3 sm:p-3.5 shadow-sm text-xs text-amber-900 flex items-start justify-between gap-3">
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
    <div class="no-print max-w-[8.5in] w-full mx-auto mb-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between bg-white p-3 sm:p-4 rounded-xl shadow-md border border-gray-200 gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ $backUrl }}" onclick="handleBack(event)" class="bg-gray-100 hover:bg-gray-200 active:bg-gray-300 text-gray-700 font-bold py-2 px-3 sm:px-3.5 rounded-lg transition-colors flex items-center gap-1.5 text-xs sm:text-sm border border-gray-300 shadow-sm cursor-pointer shrink-0" title="Go Back">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                <span>Back</span>
            </a>
            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600 shrink-0">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <div class="min-w-0">
                <h1 class="text-gray-900 font-bold text-sm sm:text-base leading-tight truncate">{{ $title }}</h1>
                <p class="text-[11px] sm:text-xs text-gray-500 mt-0.5 truncate">{{ $subtitle }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto">
            <!-- Download Button -->
            <a href="{{ $downloadUrl ?? $fileUrl }}" download="{{ $downloadFilename }}" class="flex-1 sm:flex-initial bg-green-600 hover:bg-green-700 active:bg-green-800 text-white font-bold py-2.5 px-3.5 rounded-lg transition-colors flex items-center justify-center gap-2 text-xs sm:text-sm shadow-sm text-center">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Download {{ strtoupper($extension) }}</span>
            </a>

            <!-- Print Button -->
            <button type="button" onclick="triggerPrint()" class="flex-1 sm:flex-initial bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white font-bold py-2.5 px-3.5 rounded-lg transition-colors flex items-center justify-center gap-2 text-xs sm:text-sm shadow-sm cursor-pointer">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Print Document</span>
            </button>
        </div>
    </div>

    <!-- Document Viewer Container -->
    <div class="max-w-[8.5in] w-full mx-auto flex-1 flex flex-col print-container">
        @if($extension === 'pdf')
            <!-- Mobile Friendly Notice for Phone Browsers -->
            <div class="sm:hidden no-print bg-blue-50 border border-blue-200 rounded-xl p-3 mb-3 text-xs text-blue-900 flex flex-col gap-2 shadow-sm">
                <div class="flex items-center gap-2 font-bold text-xs">
                    <span>📱</span>
                    <span>Para sa Cellphone Users:</span>
                </div>
                <p class="text-[11px] text-blue-800 leading-relaxed">
                    Kung hindi lumalabas ang PDF preview sa ibaba, gamitin ang button upang i-download o buksan direkta sa iyong PDF viewer / Google Drive:
                </p>
                <div class="flex gap-2 pt-1">
                    <a href="{{ $downloadUrl ?? $fileUrl }}" class="flex-1 bg-green-600 active:bg-green-700 text-white text-center font-bold py-2.5 px-3 rounded-lg text-xs shadow-sm flex items-center justify-center gap-1.5">
                        <span>⬇️</span> Download PDF
                    </a>
                    <a href="{{ $fileUrl }}" target="_blank" class="flex-1 bg-blue-600 active:bg-blue-700 text-white text-center font-bold py-2.5 px-3 rounded-lg text-xs shadow-sm flex items-center justify-center gap-1.5">
                        <span>👁️</span> Buksan sa Viewer
                    </a>
                </div>
            </div>

            <!-- PDF Viewer Frame -->
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
                <a href="{{ $downloadUrl ?? $fileUrl }}" download="{{ $downloadFilename }}" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-6 rounded-xl shadow transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Download File to View / Print
                </a>
            </div>
        @endif
    </div>

    <!-- Print Script -->
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
                alert('Hindi sinusuportahan ang direct printing sa loob ng Messenger.\n\nPindutin ang "Download" button o i-tap ang 3 dots (...) sa itaas at buksan sa Chrome para makapag-print.');
                return;
            }

            @if($extension === 'pdf')
                const isMobile = /Android|iPhone|iPad|iPod|webOS|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
                if (isMobile) {
                    if (confirm('Nasa cellphone ka. Upang ma-print ang PDF na ito, i-download muna ito sa iyong phone at buksan sa PDF viewer o Print service.\n\nGusto mo bang i-download ngayon?')) {
                        window.location.href = "{{ $downloadUrl ?? $fileUrl }}";
                    }
                    return;
                }
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
            try {
                window.print();
            } catch (e) {
                console.warn('window.print error:', e);
            }
        }
    </script>
</body>
</html>
