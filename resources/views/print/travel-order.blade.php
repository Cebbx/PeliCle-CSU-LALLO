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
            background-color: #f3f4f6;
            color: black;
        }
        @media print {
            body {
                background: white !important;
                color: black !important;
                font-size: 12px;
                padding: 0 !important;
                margin: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .print-container {
                min-height: auto !important;
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
    </style>
</head>
<body class="bg-gray-100 py-6 px-4">

    <!-- Floating Top Bar (hidden on print) -->
    <div class="no-print max-w-[7.5in] mx-auto mb-4 flex flex-wrap items-center justify-between bg-white p-4 rounded-xl shadow-md border border-gray-200 gap-3">
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-gray-800 font-bold text-sm mr-1">Travel Order</span>
            <!-- Interactive Purpose Selection Pills -->
            <div class="flex items-center gap-1.5 bg-gray-100 p-1 rounded-lg border border-gray-300">
                <span class="text-xs font-semibold text-gray-500 pl-1.5 mr-1">Purpose:</span>
                <button type="button" id="btnOB" onclick="togglePurposeType('business')" class="{{ $isOB ? 'bg-blue-600 text-white font-bold shadow-sm' : 'bg-white text-gray-700 hover:bg-gray-200' }} py-1 px-2.5 rounded text-xs transition-colors flex items-center gap-1">
                    <span>✓</span> Official Business
                </button>
                <button type="button" id="btnOT" onclick="togglePurposeType('time')" class="{{ !$isOB ? 'bg-blue-600 text-white font-bold shadow-sm' : 'bg-white text-gray-700 hover:bg-gray-200' }} py-1 px-2.5 rounded text-xs transition-colors flex items-center gap-1">
                    <span>✓</span> Official Time
                </button>
            </div>
            <!-- Transportation Allowed toggle pill -->
            <button type="button" id="btnTA" onclick="toggleTransAllowed()" class="bg-emerald-600 text-white font-bold shadow-sm py-1.5 px-3 rounded-lg text-xs transition-colors flex items-center gap-1.5 ml-1 border border-emerald-700">
                <span id="btnTAIcon">✓</span> Transportation Allowed
            </button>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('trip-tickets.print', $ticket->id) }}" target="_blank" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-3 rounded-lg transition-colors flex items-center gap-1.5 text-xs shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Trip Ticket
            </a>
            <button onclick="downloadPDF()" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-3 rounded-lg transition-colors flex items-center gap-1.5 text-xs shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Download PDF
            </button>
            <button onclick="window.print()" class="bg-sky-600 hover:bg-sky-700 text-white font-bold py-2 px-3 rounded-lg transition-colors flex items-center gap-1.5 text-xs shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Print Document
            </button>
        </div>
    </div>

    <!-- Main Travel Order Sheet (Matches Official CSU Photo Exactly) -->
    <div class="bg-white w-full max-w-[7.5in] mx-auto p-8 sm:p-10 flex flex-col justify-between print-container relative shadow-lg border border-gray-200 text-black text-[13px] leading-relaxed" style="min-height: 10.2in;">
        
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
