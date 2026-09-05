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
<body class="bg-gray-100 py-6 px-4 min-h-screen flex flex-col">

    <!-- Floating Top Bar (hidden on print) -->
    <div class="no-print max-w-[8.5in] w-full mx-auto mb-4 flex flex-wrap items-center justify-between bg-white p-4 rounded-xl shadow-md border border-gray-200 gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <div>
                <h1 class="text-gray-900 font-bold text-base leading-tight">{{ $title }}</h1>
                <p class="text-xs text-gray-500 mt-0.5">{{ $subtitle }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <!-- Download Button -->
            <a href="{{ $fileUrl }}" download="{{ $downloadFilename }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition-colors flex items-center gap-2 text-sm shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Download {{ strtoupper($extension) }} File
            </a>

            <!-- Print Button -->
            <button onclick="triggerPrint()" class="bg-sky-600 hover:bg-sky-700 text-white font-bold py-2 px-4 rounded-lg transition-colors flex items-center gap-2 text-sm shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Print Document
            </button>
        </div>
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

    <!-- Print Script -->
    <script>
        function triggerPrint() {
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
    </script>

</body>
</html>
