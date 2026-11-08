<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Vehicle Utilization Report - {{ $monthName }}</title>
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
                font-size: 11px;
                padding: 0 !important;
                margin: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            @page {
                size: letter portrait;
                margin: 0.4in;
            }
            .print-container {
                padding: 0 !important;
                margin: 0 !important;
                box-shadow: none !important;
                border: none !important;
                max-width: 100% !important;
                width: 100% !important;
            }
        }
    </style>
</head>
<body class="bg-gray-100 py-4 px-2 sm:px-6">

    <!-- Top Action Bar (Sticky, Mobile Responsive, hidden on print) -->
    <div class="no-print sticky top-2 z-50 max-w-5xl mx-auto mb-4 bg-white/95 backdrop-blur-sm p-3 sm:p-4 rounded-xl shadow-lg border border-gray-200 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3 flex-wrap">
            <div>
                <h1 class="text-gray-800 font-extrabold text-xs sm:text-base">Monthly Vehicle Utilization Report</h1>
                <p class="text-[10px] sm:text-xs text-gray-500 font-mono">Period: {{ $monthName }} &bull; GSO Motorpool</p>
            </div>

            <!-- Month Dropdown Selector -->
            <label class="text-xs font-bold text-gray-700 flex items-center gap-1.5 bg-gray-50 border border-gray-300 rounded-lg px-2.5 py-1.5 shadow-sm">
                <span>📅 Month:</span>
                <select onchange="changeMonth(this.value)" class="bg-white border border-gray-300 rounded px-2 py-1 text-xs font-semibold text-gray-800 cursor-pointer focus:ring-2 focus:ring-blue-500">
                    @foreach($availableMonths as $val => $lbl)
                        <option value="{{ $val }}" {{ ($year . '-' . $month) === $val ? 'selected' : '' }}>
                            {{ $lbl }}
                        </option>
                    @endforeach
                </select>
            </label>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto justify-end flex-wrap">
            <!-- Switcher Button to Total Trips Logbook -->
            <a href="{{ route('logbook.print', ['year' => $year, 'month' => $month]) }}" class="bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold py-2 px-3 rounded-lg text-xs transition border border-blue-200 flex items-center gap-1.5 shadow-sm">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>View Total Trips Logbook</span>
            </a>

            <!-- Back Button -->
            <button onclick="if (window.history.length > 1) { window.history.back(); } else { window.close(); }" class="bg-gray-100 hover:bg-gray-200 active:scale-95 text-gray-700 font-bold py-2 px-3 rounded-lg text-xs transition flex items-center gap-1.5 border border-gray-300">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Back</span>
            </button>

            <!-- Download PDF Button -->
            <button id="btnDownloadPdf" onclick="downloadPDF(this)" class="bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold py-2 px-3.5 rounded-lg text-xs transition flex items-center gap-1.5 shadow">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Download PDF</span>
            </button>

            <!-- Print Button -->
            <button onclick="window.print()" class="bg-sky-600 hover:bg-sky-700 active:scale-95 text-white font-bold py-2 px-3.5 rounded-lg text-xs transition flex items-center gap-1.5 shadow">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Print Report</span>
            </button>
        </div>
    </div>

    <!-- Main Printable Sheet -->
    <div class="print-container max-w-5xl mx-auto bg-white p-6 sm:p-8 border border-black shadow-lg text-black">
        
        <!-- Header -->
        <div class="border-b-2 border-black pb-3 mb-4">
            <div class="flex items-center justify-between gap-4">
                <img src="{{ asset('csu-logo.png') }}" class="w-16 h-16 sm:w-20 sm:h-20 object-contain" alt="CSU Logo" crossorigin="anonymous" />
                <div class="text-center flex-1">
                    <p class="text-[10px] tracking-widest uppercase font-semibold text-gray-700">Republic of the Philippines</p>
                    <h2 class="text-base sm:text-lg font-black text-black uppercase tracking-wide">Cagayan State University</h2>
                    <p class="text-xs font-bold text-gray-800">Lal-lo Campus &bull; Lal-lo, Cagayan</p>
                    <p class="text-[11px] font-bold text-gray-700 uppercase mt-0.5">GENERAL SERVICES OFFICE &bull; MOTORPOOL SECTION</p>
                    <h3 class="text-xs sm:text-sm font-black uppercase text-black mt-2 underline tracking-wider">
                        MONTHLY VEHICLE UTILIZATION & FLEET ACCOMPLISHMENT REPORT
                    </h3>
                    <p class="text-xs font-bold text-gray-800 uppercase mt-0.5">FOR THE MONTH OF {{ strtoupper($monthName) }}</p>
                </div>
                <div class="w-20 text-right font-mono text-[9px] text-gray-600">
                    <div>F-GSO-VEH-UTIL</div>
                    <div>Rev. 01</div>
                </div>
            </div>
        </div>

        <!-- Monthly KPI Metrics -->
        <div class="grid grid-cols-4 gap-2 mb-4 text-center">
            <div class="border border-black p-2 bg-gray-50">
                <div class="text-[8.5px] uppercase font-bold text-gray-600">Total Fleet Units</div>
                <div class="text-lg font-black text-black">{{ count($allVehicles) }}</div>
                <div class="text-[8px] text-gray-600">Campus Vehicles</div>
            </div>
            <div class="border border-black p-2 bg-gray-50">
                <div class="text-[8.5px] uppercase font-bold text-gray-600">Vehicles Dispatched</div>
                <div class="text-lg font-black text-black">{{ $activeVehiclesCount }}</div>
                <div class="text-[8px] text-gray-600">Active this Month</div>
            </div>
            <div class="border border-black p-2 bg-gray-50">
                <div class="text-[8.5px] uppercase font-bold text-gray-600">Total Monthly Trips</div>
                <div class="text-lg font-black text-black">{{ $totalFleetTrips }}</div>
                <div class="text-[8px] text-gray-600">Official Dispatches</div>
            </div>
            <div class="border border-black p-2 bg-gray-50">
                <div class="text-[8.5px] uppercase font-bold text-gray-600">Passengers Transported</div>
                <div class="text-lg font-black text-black">{{ $totalFleetPassengers }}</div>
                <div class="text-[8px] text-gray-600">Faculty, Staff & Students</div>
            </div>
        </div>

        <!-- Vehicle Utilization Table -->
        <div class="mb-6">
            <h4 class="text-xs font-bold uppercase tracking-wider mb-2 text-black border-b border-black pb-0.5 flex items-center justify-between">
                <span>Vehicle Fleet Dispatch & Utilization Summary</span>
                <span class="text-[9px] font-normal text-gray-600">Sorted by utilization frequency</span>
            </h4>

            <table class="w-full border-collapse border border-black text-[10px]">
                <thead>
                    <tr class="bg-gray-100 font-bold text-center border-b border-black">
                        <th class="border border-black p-1.5 w-8">#</th>
                        <th class="border border-black p-1.5 text-left">PLATE NUMBER</th>
                        <th class="border border-black p-1.5 text-left">BRAND & MODEL</th>
                        <th class="border border-black p-1.5 text-center">TYPE</th>
                        <th class="border border-black p-1.5 text-center">TOTAL TRIPS</th>
                        <th class="border border-black p-1.5 text-center">COMPLETED</th>
                        <th class="border border-black p-1.5 text-center">PASSENGERS</th>
                        <th class="border border-black p-1.5 text-left">ASSIGNED DRIVER(S)</th>
                        <th class="border border-black p-1.5 text-center">STATUS</th>
                        <th class="border border-black p-1.5 text-center">PMS SCHEDULE</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vehicleStats as $idx => $stat)
                        <tr class="border-b border-black hover:bg-gray-50">
                            <td class="border border-black p-1.5 text-center font-bold">{{ $idx + 1 }}</td>
                            <td class="border border-black p-1.5 font-mono font-bold whitespace-nowrap">{{ $stat['plate'] }}</td>
                            <td class="border border-black p-1.5 font-semibold">{{ $stat['brand_model'] }}</td>
                            <td class="border border-black p-1.5 text-center uppercase text-[9px]">{{ $stat['type'] }}</td>
                            <td class="border border-black p-1.5 text-center font-bold text-base {{ $stat['trips_count'] > 0 ? 'text-blue-900' : 'text-gray-400' }}">
                                {{ $stat['trips_count'] }}
                            </td>
                            <td class="border border-black p-1.5 text-center font-bold text-emerald-800">
                                {{ $stat['completed_count'] }}
                            </td>
                            <td class="border border-black p-1.5 text-center font-bold">
                                {{ $stat['passengers'] }}
                            </td>
                            <td class="border border-black p-1.5 text-[9px]">
                                @if(!empty($stat['frequent_drivers']))
                                    {{ implode(', ', $stat['frequent_drivers']) }}
                                @else
                                    <span class="text-gray-400 italic">No Trips</span>
                                @endif
                            </td>
                            <td class="border border-black p-1.5 text-center uppercase text-[9px] font-bold">
                                @if($stat['status'] === 'maintenance')
                                    <span class="text-red-600">Maintenance</span>
                                @elseif($stat['active_count'] > 0)
                                    <span class="text-blue-600">On Trip</span>
                                @else
                                    <span class="text-emerald-700">Available</span>
                                @endif
                            </td>
                            <td class="border border-black p-1.5 text-center text-[9px] whitespace-nowrap">
                                @if($stat['is_overdue'])
                                    <span class="text-red-600 font-bold">{{ $stat['next_pms'] }} (Overdue)</span>
                                @elseif($stat['is_due_soon'])
                                    <span class="text-amber-600 font-bold">{{ $stat['next_pms'] }} (Due)</span>
                                @else
                                    <span class="text-gray-700">{{ $stat['next_pms'] }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="border border-black p-6 text-center text-gray-500 font-semibold">
                                No vehicles recorded in the system.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Signatures & Verification -->
        <div class="mt-8 grid grid-cols-2 gap-12 text-xs">
            <div>
                <div class="font-bold uppercase mb-8">Prepared by:</div>
                <div class="border-b border-black w-72 pb-0.5 text-center font-bold uppercase">
                    JOEL A. TUMAMAO
                </div>
                <div class="text-[10px] text-gray-700 uppercase w-72 text-center">General Services Office</div>
            </div>

            <div class="text-right flex flex-col items-end">
                <div class="font-bold uppercase mb-8 self-end">Noted by:</div>
                <div class="border-b border-black w-72 pb-0.5 text-center font-bold uppercase self-end text-[11px] whitespace-nowrap">
                    {{ \App\Models\User::getCeoName() }}
                </div>
                <div class="text-[10px] text-gray-700 uppercase self-end w-72 text-center">Campus Executive Officer</div>
            </div>
        </div>

        <!-- Footer Code -->
        <div class="mt-6 pt-2 border-t border-black flex justify-between items-center text-[9px] font-mono text-gray-600">
            <span>F-GSO-VEH-UTIL-61202</span>
            <span>Cagayan State University Lal-lo Campus &bull; GSO Motorpool Section</span>
        </div>
    </div>

    <!-- Script for Month Changing & PDF Download -->
    <script>
        function changeMonth(val) {
            if (!val) return;
            const parts = val.split('-');
            const year = parts[0];
            const month = parts[1];
            window.location.href = "{{ route('logbook.vehicle-usage.print') }}?year=" + year + "&month=" + month;
        }

        async function downloadPDF(btn) {
            const btnEl = btn || document.querySelector('#btnDownloadPdf');
            const originalHtml = btnEl ? btnEl.innerHTML : '';

            if (typeof html2pdf === 'undefined') {
                alert('PDF engine is loading. You can use "Print Report" to Save as PDF directly!');
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
            const opt = {
                margin:       [0.3, 0.3, 0.3, 0.3],
                filename:     'CSU_Lallo_GSO_Vehicle_Utilization_{{ $year }}_{{ $month }}.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true, logging: false },
                jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
            };

            try {
                await html2pdf().set(opt).from(element).save();
            } catch (err) {
                console.error('PDF error:', err);
                window.print();
            } finally {
                if (btnEl) {
                    btnEl.disabled = false;
                    btnEl.innerHTML = originalHtml;
                }
            }
        }
    </script>
</body>
</html>
