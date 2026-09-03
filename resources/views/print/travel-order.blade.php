<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Travel Order - {{ $ticket->ticket_number }}</title>
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
            }
            @page {
                size: letter;
                margin: 0.5in;
            }
        }
        .form-line {
            border-bottom: 1.5px solid black;
            display: inline-block;
            padding-left: 8px;
            padding-right: 8px;
        }
    </style>
</head>
<body class="bg-gray-100 py-6 px-4">

    <!-- Floating Top Bar (hidden on print) -->
    <div class="no-print max-w-[8.5in] mx-auto mb-4 flex items-center justify-between bg-white p-4 rounded-xl shadow-md border border-gray-200 gap-4">
        <span class="text-gray-700 font-bold text-sm">
            Travel Order ({{ ucfirst($type) }})
        </span>
        <div class="flex gap-2">
            <button onclick="downloadPDF()" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition-colors flex items-center gap-2 text-sm shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Download PDF File
            </button>
            <button onclick="window.print()" class="bg-sky-600 hover:bg-sky-700 text-white font-bold py-2 px-4 rounded-lg transition-colors flex items-center gap-2 text-sm shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Print Document
            </button>
        </div>
    </div>

    <!-- Main Travel Order Sheet -->
    <div class="bg-white w-full max-w-[8.5in] mx-auto p-8 sm:p-12 flex flex-col print-container relative" style="min-height: 10.5in; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1); border: 1px solid #e5e7eb;">
        
        <!-- Header -->
        <div class="w-full pb-4 mb-4 flex justify-center relative">
            <img src="/csu-logo.png" alt="CSU Logo" class="absolute left-4 top-0 w-16 h-16 object-contain" />
            <div class="text-center">
                <h2 class="text-xs uppercase tracking-wider text-black">Republic of the Philippines</h2>
                <h1 class="text-base font-extrabold uppercase text-black tracking-wide">Cagayan State University</h1>
                <h3 class="text-xs text-black">Lal-lo, Cagayan</h3>
                <h2 class="text-sm font-extrabold uppercase text-black tracking-wider mt-3 border-b-2 border-black inline-block pb-0.5">Travel Order</h2>
                <div class="text-xs text-black mt-1">Series of {{ date('Y') }} ____ - ____</div>
            </div>
        </div>

        <!-- Form Fields (Interactive & Editable) -->
        <div class="space-y-4 mt-6 text-xs text-black leading-loose">
            
            <div class="flex items-end">
                <span class="w-20 font-bold">Name:</span>
                <input type="text" value="{{ $name }}" class="flex-grow border-b border-black outline-none px-2 font-bold text-sm h-6 bg-transparent" placeholder="Enter Full Name" />
            </div>

            <div class="flex items-end">
                <span class="w-20 font-bold">Position:</span>
                <input type="text" value="{{ $position }}" class="flex-grow border-b border-black outline-none px-2 h-6 bg-transparent" placeholder="Enter Position" />
            </div>

            <div class="flex items-end gap-4">
                <span class="w-20 font-bold">Departure:</span>
                <input type="text" value="{{ $departure }}" class="flex-grow border-b border-black outline-none px-2 h-6 bg-transparent" placeholder="Departure Date/Time" />
                <span class="font-bold">Arrival:</span>
                <input type="text" value="{{ $arrival }}" class="flex-grow border-b border-black outline-none px-2 h-6 bg-transparent" placeholder="Arrival Date/Time" />
            </div>

            <div class="flex items-end gap-4">
                <span class="w-20 font-bold">Station:</span>
                <input type="text" value="CSU Lal-lo Campus" class="flex-grow border-b border-black outline-none px-2 h-6 bg-transparent" />
                <span class="font-bold">Destination:</span>
                <input type="text" value="{{ $destination }}" class="flex-grow border-b border-black outline-none px-2 h-6 bg-transparent font-bold" placeholder="Destination" />
            </div>

            <div class="flex flex-col mt-2">
                <span class="font-bold mb-1">Purpose:</span>
                <textarea class="w-full border-b border-black outline-none px-2 py-1 min-h-[48px] bg-transparent resize-none leading-relaxed" placeholder="Purpose of Travel">{{ $purpose }}</textarea>
            </div>

            <!-- Checkboxes and Details -->
            <div class="grid grid-cols-2 gap-8 pt-4">
                <div class="flex flex-col gap-2">
                    <label class="flex items-center gap-3 cursor-default pointer-events-none">
                        <input type="checkbox" checked disabled class="w-4 h-4 accent-black pointer-events-none cursor-default" />
                        <span class="font-bold uppercase tracking-wider text-[10px]">Official Business</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-default pointer-events-none">
                        <input type="checkbox" disabled class="w-4 h-4 accent-black pointer-events-none cursor-default" />
                        <span class="font-bold uppercase tracking-wider text-[10px]">Official Time</span>
                    </label>
                </div>
                <div class="space-y-3">
                    <div class="flex items-end">
                        <span class="w-36 font-semibold">Transportation Allowed:</span>
                        <input type="text" value="{{ $vehicleName ?: 'Government Vehicle' }}" class="flex-grow border-b border-black outline-none px-2 h-5 bg-transparent font-bold" placeholder="Assigned Vehicle" />
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-8 pt-2">
                <div class="flex items-end">
                    <span class="w-36 font-semibold">Travel Charged Against:</span>
                    <input type="text" value="Local/GAA Funds" class="flex-grow border-b border-black outline-none px-2 h-5 bg-transparent" placeholder="Charged Against" />
                </div>
                <div class="flex items-end">
                    <span class="w-16 font-semibold">Remarks:</span>
                    <input type="text" value="Subject to usual accounting rules" class="flex-grow border-b border-black outline-none px-2 h-5 bg-transparent" placeholder="Remarks" />
                </div>
            </div>

        </div>

        <!-- Approvals Row (Editable Signatories) -->
        <div class="mt-12 text-xs text-black">
            
            <div class="grid grid-cols-2 gap-16 mt-8">
                
                <!-- Recommending Approval -->
                <div class="flex flex-col justify-end min-h-[80px] text-center">
                    <span class="text-[9px] uppercase font-bold text-gray-500 mb-4 text-left">Recommending Approval:</span>
                    <input type="text" value="Joel A. Tumamao" class="text-xs font-bold text-black border-b border-black pb-0.5 text-center w-full uppercase bg-transparent outline-none" />
                    <input type="text" value="GSO / Immediate Supervisor" class="text-[9px] text-black text-center font-bold uppercase mt-1 bg-transparent outline-none" />
                    <span class="text-[8px] font-mono text-gray-600 mt-1">Recommended: {{ \Carbon\Carbon::parse($ticket->created_at)->format('M d, Y - h:i A') }}</span>
                </div>

                <!-- Approved -->
                <div class="flex flex-col justify-end min-h-[80px] text-center">
                    <span class="text-[9px] uppercase font-bold text-gray-500 mb-4 text-left">Approved:</span>
                    <input type="text" value="James B. Cabildo, PhD, ASEAN Engr." class="text-xs font-bold text-black border-b border-black pb-0.5 text-center w-full uppercase bg-transparent outline-none" />
                    <input type="text" value="Campus Executive Officer" class="text-[9px] text-black text-center font-bold uppercase mt-1 bg-transparent outline-none" />
                    <span class="text-[8px] font-mono font-bold text-emerald-800 bg-emerald-50 border border-emerald-300 px-1 py-0.5 rounded mt-1 inline-block">
                        ✓ Approved Timestamp: {{ \Carbon\Carbon::parse($ticket->created_at)->format('M d, Y - h:i:s A') }}
                    </span>
                </div>

            </div>

        </div>

        <!-- Appearance Certified Section -->
        <div class="mt-12 border border-black p-4 text-xs text-black">
            <span class="text-[10px] font-extrabold uppercase tracking-wide block mb-3 border-b border-black pb-1">Appearance Certified:</span>
            
            <div class="grid grid-cols-2 gap-8">
                <div class="border-r border-gray-300 pr-4">
                    <div class="flex justify-between items-end mb-6">
                        <span class="font-bold">Date:</span>
                        <input type="text" class="w-2/3 border-b border-black h-4 bg-transparent outline-none px-2" />
                    </div>
                    <div class="border-t border-black text-center pt-1 text-[9px] uppercase font-bold">Signature</div>
                </div>
                <div>
                    <div class="flex justify-between items-end mb-6">
                        <span class="font-bold">Name:</span>
                        <input type="text" class="w-2/3 border-b border-black h-4 bg-transparent outline-none px-2" />
                    </div>
                    <div class="border-t border-black text-center pt-1 text-[9px] uppercase font-bold">Name & Signature of Person Visited</div>
                </div>
            </div>
        </div>

        <!-- Spacer to push metadata to the bottom of US Letter page -->
        <div class="flex-grow"></div>

        <!-- Document Metadata footer details -->
        <div class="pt-4 flex justify-between items-center text-[9px] text-black font-mono border-t border-black mt-8">
            <span>F-OCEO-60105</span>
            <span>Rev. 01, 01-03-2024</span>
        </div>
    </div>

    <!-- PDF Download Script -->
    <script>
        function downloadPDF() {
            const element = document.querySelector('.print-container');
            const opt = {
                margin:       0.4,
                filename:     'Travel-Order-{{ $ticket->ticket_number }}.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true },
                jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
            };
            html2pdf().set(opt).from(element).save();
        }
    </script>

</body>
</html>
