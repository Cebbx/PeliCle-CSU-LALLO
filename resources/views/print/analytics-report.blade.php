<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fleet Analytics Summary Report - {{ date('F Y') }}</title>
    <script src="{{ asset('js/tailwindcss.js') }}"></script>
    <script src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <style>
        body {
            font-family: 'Arial', 'Calibri', sans-serif;
            background-color: #f3f4f6;
            color: black;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
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
            @page {
                size: letter;
                margin: 0.4in;
            }
            .print-container {
                padding: 0 !important;
                margin: 0 !important;
                min-height: auto !important;
                box-shadow: none !important;
                border: none !important;
                max-width: 100% !important;
                width: 100% !important;
            }
        }
    </style>
</head>
<body class="bg-gray-100 py-3 sm:py-6 px-2 sm:px-4">

    <!-- Top Action Bar (Sticky, Mobile Responsive, hidden on print) -->
    <div class="no-print sticky top-2 z-50 max-w-[8.5in] mx-auto mb-4 bg-white/95 backdrop-blur-sm p-3 sm:p-4 rounded-xl shadow-lg border border-gray-200 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-gray-800 font-extrabold text-xs sm:text-base">Fleet Analytics & Dispatch Summary Report</h1>
            <p class="text-[10px] sm:text-xs text-gray-500 font-mono">Generated: {{ \Carbon\Carbon::now('Asia/Manila')->format('F d, Y - h:i A') }}</p>
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
                <span>Download PDF</span>
            </button>
            <button id="btnPrintDoc" onclick="triggerPrint(this)" class="flex-1 sm:flex-initial bg-sky-600 hover:bg-sky-700 active:scale-95 text-white font-bold py-2.5 px-3.5 sm:px-4 rounded-lg transition-all flex items-center justify-center gap-2 text-xs sm:text-sm shadow-md cursor-pointer select-none">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Print Report</span>
            </button>
        </div>
    </div>

    <!-- Main Printable Sheet -->
    <div class="print-container max-w-[8.5in] mx-auto bg-white p-8 border border-gray-300 shadow-lg text-black">
        
        <!-- Header -->
        <div class="border-b-2 border-black pb-4 mb-4">
            <div class="flex items-center justify-between gap-4">
                <img src="{{ asset('csu-logo.png') }}" class="w-20 h-20 object-contain" alt="CSU Logo" crossorigin="anonymous" />
                <div class="text-center flex-1">
                    <p class="text-[10px] tracking-widest uppercase font-semibold text-gray-700">Republic of the Philippines</p>
                    <h2 class="text-base font-extrabold text-black uppercase tracking-wide">Cagayan State University</h2>
                    <p class="text-xs font-bold text-gray-800">Lal-lo Campus</p>
                    <p class="text-[11px] font-semibold text-gray-700">GENERAL SERVICES OFFICE (GSO)</p>
                    <h3 class="text-sm font-black uppercase text-black mt-2 underline tracking-wider">
                        FLEET DISPATCH & VEHICLE UTILIZATION REPORT
                    </h3>
                </div>
                <div class="w-20 text-right font-mono text-[9px] text-gray-600">
                    <div>F-GSO-ANL</div>
                    <div>Rev. 02</div>
                </div>
            </div>
        </div>

        <!-- Filter Meta Bar -->
        <div class="grid grid-cols-3 gap-2 bg-gray-50 border border-black p-2.5 mb-4 text-[11px]">
            <div>
                <span class="font-bold">Period Covered:</span>
                <span>
                    @if($startDate && $endDate)
                        {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                    @elseif($startDate)
                        From {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }}
                    @else
                        All Recorded Periods
                    @endif
                </span>
            </div>
            <div>
                <span class="font-bold">Department Scope:</span>
                <span>{{ $department ?: 'All Departments' }}</span>
            </div>
            <div>
                <span class="font-bold">Trip Status Filter:</span>
                <span>{{ $status ? ucfirst($status) : 'All Statuses' }}</span>
            </div>
        </div>

        <!-- Executive KPI Highlights -->
        <div class="grid grid-cols-3 gap-2 mb-4 text-center">
            <div class="border border-black p-2 bg-gray-50">
                <div class="text-[9px] uppercase font-bold text-gray-600">Total Requests</div>
                <div class="text-lg font-black text-black">{{ $totalRequests }}</div>
                <div class="text-[9px] text-gray-600">{{ $approvedCount }} Approved / Completed</div>
            </div>
            <div class="border border-black p-2 bg-gray-50">
                <div class="text-[9px] uppercase font-bold text-gray-600">Approval Rate</div>
                <div class="text-lg font-black text-black">{{ $approvalRate }}%</div>
                <div class="text-[9px] text-gray-600">{{ $rejectedCount }} Disapproved / Cancelled</div>
            </div>
            <div class="border border-black p-2 bg-gray-50">
                <div class="text-[9px] uppercase font-bold text-gray-600">Total Passengers</div>
                <div class="text-lg font-black text-black">{{ $totalPassengers }}</div>
                <div class="text-[9px] text-gray-600">Clients Transported</div>
            </div>
        </div>

        <!-- Section 1: Department Distribution Table -->
        <div class="mb-4">
            <h4 class="text-xs font-bold uppercase tracking-wider mb-1.5 text-black border-b border-black pb-0.5">
                1. Departmental Trip Distribution
            </h4>
            <table class="w-full border-collapse border border-black text-[10px]">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-black p-1.5 text-left font-bold">College / Department Unit</th>
                        <th class="border border-black p-1.5 text-center font-bold">Number of Trip Requests</th>
                        <th class="border border-black p-1.5 text-center font-bold">Share of Total Fleet Demand</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deptBreakdown as $deptName => $count)
                        @php
                            $percentage = $totalRequests > 0 ? round(($count / $totalRequests) * 100, 1) : 0;
                        @endphp
                        <tr>
                            <td class="border border-black p-1.5 font-semibold">{{ $deptName }}</td>
                            <td class="border border-black p-1.5 text-center">{{ $count }} request(s)</td>
                            <td class="border border-black p-1.5 text-center">{{ $percentage }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="border border-black p-2 text-center text-gray-500 italic">No departmental trip data recorded for this scope.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Section 2: Detailed Accomplished Trip Log -->
        <div class="mb-6">
            <h4 class="text-xs font-bold uppercase tracking-wider mb-1.5 text-black border-b border-black pb-0.5">
                2. Official Trip Log Summary Sheet
            </h4>
            <table class="w-full border-collapse border border-black text-[9px]">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-black p-1 text-center font-bold">Req #</th>
                        <th class="border border-black p-1 text-left font-bold">Requester (Unit)</th>
                        <th class="border border-black p-1 text-left font-bold">Destination & Purpose</th>
                        <th class="border border-black p-1 text-center font-bold">Travel Date</th>
                        <th class="border border-black p-1 text-left font-bold">Vehicle & Driver</th>
                        <th class="border border-black p-1 text-center font-bold">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                        <tr>
                            <td class="border border-black p-1 text-center font-mono font-bold">{{ $req->request_number }}</td>
                            <td class="border border-black p-1">
                                <div class="font-bold text-black">{{ $req->employee_name }}</div>
                                <div class="text-[8px] text-gray-600">{{ $req->department }}</div>
                            </td>
                            <td class="border border-black p-1">
                                <div class="font-semibold">{{ $req->destination }}</div>
                                <div class="text-[8px] text-gray-600">{{ $req->purpose }}</div>
                            </td>
                            <td class="border border-black p-1 text-center">
                                <div>{{ \Carbon\Carbon::parse($req->date)->format('M d, Y') }}</div>
                                <div class="text-[8px] text-gray-600">{{ $req->time ? \Carbon\Carbon::parse($req->time)->format('g:i A') : '' }}</div>
                            </td>
                            <td class="border border-black p-1">
                                <div>{{ $req->vehicle ?? 'N/A' }}</div>
                                <div class="text-[8px] text-gray-600 font-semibold">{{ $req->tripTicket?->driver?->name ?? 'No Driver' }}</div>
                            </td>
                            <td class="border border-black p-1 text-center font-bold">
                                {{ strtoupper($req->status) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="border border-black p-3 text-center text-gray-500 italic">No trip records found for the selected filter parameters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Administrative Signatures -->
        <div class="mt-8 pt-4 border-t border-black text-xs">
            <div class="grid grid-cols-3 gap-6 text-center">
                <div class="flex flex-col justify-end">
                    <span class="font-bold text-black uppercase">{{ \App\Models\User::getGsoName() }}</span>
                    <span class="text-[9px] uppercase text-gray-700 font-semibold">{{ \App\Models\User::getGsoTitle() }}</span>
                    <div class="border-t border-black pt-1 mt-1 font-bold text-[9px] uppercase">Prepared by:</div>
                    <span class="text-[8px] font-mono text-gray-600 mt-1">✓ Digital Record: {{ \Carbon\Carbon::now('Asia/Manila')->format('M d, Y') }}</span>
                </div>

                <div class="flex flex-col justify-end">
                    <span class="font-bold text-black uppercase">{{ \App\Models\User::getCaoName() }}</span>
                    <span class="text-[9px] uppercase text-gray-700 font-semibold">Chief Administrative Officer</span>
                    <div class="border-t border-black pt-1 mt-1 font-bold text-[9px] uppercase">Verified by:</div>
                    <span class="text-[8px] font-mono text-gray-600 mt-1">✓ Verified Record</span>
                </div>

                <div class="flex flex-col justify-end">
                    <span class="font-bold text-black uppercase">{{ \App\Models\User::getCeoName() }}</span>
                    <span class="text-[9px] uppercase text-gray-700 font-semibold">{{ \App\Models\User::getCeoTitle() }}</span>
                    <div class="border-t border-black pt-1 mt-1 font-bold text-[9px] uppercase">Approved by:</div>
                    <span class="text-[8px] font-mono font-bold text-emerald-800 bg-emerald-50 border border-emerald-300 px-1 py-0.5 rounded mt-1 inline-block">
                        ✓ Certified Official Report
                    </span>
                </div>
            </div>
        </div>

        <!-- Document Footer Code -->
        <div class="mt-6 pt-2 border-t border-gray-300 flex justify-between text-[9px] text-gray-500 font-mono">
            <span>CSU-SM General Services Fleet Management System</span>
            <span>Document Control No. CSU-GSO-RPT-{{ date('Y') }}</span>
        </div>

    </div>

    <!-- Mobile-Optimized PDF Download & Print Scripts -->
    <script>
        async function downloadPDF(btn) {
            const btnEl = btn || document.querySelector('#btnDownloadPdf');
            const originalHtml = btnEl ? btnEl.innerHTML : '';

            if (typeof html2pdf === 'undefined') {
                alert('PDF engine is still loading. Please wait a few seconds or use "Print Report" to Save as PDF directly!');
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
            const filename = 'Fleet-Analytics-Report-{{ date('Y-m-d') }}.pdf';

            const opt = {
                margin:       [0.25, 0.25, 0.25, 0.25],
                filename:     filename,
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  {
                    scale: 2,
                    useCORS: true,
                    allowTaint: true,
                    logging: false,
                    scrollX: 0,
                    scrollY: 0,
                    windowWidth: 816,
                    onclone: function(clonedDoc) {
                        clonedDoc.body.style.width = '816px';
                        clonedDoc.body.style.minWidth = '816px';
                        clonedDoc.body.style.margin = '0 auto';
                        clonedDoc.body.style.padding = '0';
                        clonedDoc.body.style.background = '#ffffff';

                        const el = clonedDoc.querySelector('.print-container');
                        if (el) {
                            el.style.width = '768px';
                            el.style.maxWidth = '768px';
                            el.style.minWidth = '768px';
                            el.style.margin = '0 auto';
                            el.style.padding = '20px 24px';
                            el.style.minHeight = '0';
                            el.style.height = 'auto';
                            el.style.boxShadow = 'none';
                            el.style.border = 'none';
                        }
                    }
                },
                jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' },
                pagebreak: { mode: ['avoid-all', 'css', 'legacy'] }
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
