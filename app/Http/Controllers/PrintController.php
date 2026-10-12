<?php

namespace App\Http\Controllers;

use App\Models\VehicleRequest;
use App\Models\TripTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PrintController extends Controller
{
    public function printRequest($id)
    {
        $request = VehicleRequest::findOrFail($id);
        return response()
            ->view('print.vehicle-request', compact('request'))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function viewSignedDocument($id)
    {
        $request = VehicleRequest::findOrFail($id);
        
        if (!$request->document) {
            abort(404, 'No signed document has been uploaded for this request yet.');
        }

        $fileUrl = asset('storage/' . $request->document);
        $extension = strtolower(pathinfo($request->document, PATHINFO_EXTENSION));
        $downloadFilename = 'CEO-Signed-Document-' . $request->request_number . '.' . $extension;

        return view('print.signed-document', [
            'record' => $request,
            'type' => 'vehicle-request',
            'title' => 'CEO Signed Document - ' . $request->request_number,
            'subtitle' => 'Requester: ' . $request->employee_name . ' (' . $request->department . ') | Date: ' . \Carbon\Carbon::parse($request->date)->format('M d, Y'),
            'fileUrl' => $fileUrl,
            'extension' => $extension,
            'fileName' => basename($request->document),
            'downloadFilename' => $downloadFilename,
            'downloadUrl' => route('vehicle-requests.download-signed-document', $request->id),
        ]);
    }

    public function downloadSignedDocument($id)
    {
        $request = VehicleRequest::findOrFail($id);
        
        if (!$request->document || !Storage::disk('public')->exists($request->document)) {
            abort(404, 'No signed document found for this vehicle request.');
        }

        $extension = strtolower(pathinfo($request->document, PATHINFO_EXTENSION));
        $downloadFilename = 'CEO-Signed-Document-' . $request->request_number . '.' . $extension;

        return Storage::disk('public')->download($request->document, $downloadFilename);
    }

    public function viewTripTicketSignedDocument($id)
    {
        $ticket = TripTicket::with('vehicleRequest')->findOrFail($id);
        
        $doc = $ticket->document ?: $ticket->vehicleRequest?->document;

        if (!$doc) {
            abort(404, 'No signed document has been uploaded for this trip ticket yet.');
        }

        $fileUrl = asset('storage/' . $doc);
        $extension = strtolower(pathinfo($doc, PATHINFO_EXTENSION));
        $downloadFilename = 'CEO-Signed-Document-' . $ticket->ticket_number . '.' . $extension;

        return view('print.signed-document', [
            'record' => $ticket,
            'type' => 'trip-ticket',
            'title' => 'CEO Signed Document - ' . $ticket->ticket_number,
            'subtitle' => 'Driver: ' . ($ticket->driver?->name ?? 'N/A') . ' | Vehicle: ' . \App\Models\Vehicle::getVehicleName($ticket->vehicle),
            'fileUrl' => $fileUrl,
            'extension' => $extension,
            'fileName' => basename($doc),
            'downloadFilename' => $downloadFilename,
            'downloadUrl' => route('trip-tickets.download-signed-document', $ticket->id),
        ]);
    }

    public function downloadTripTicketSignedDocument($id)
    {
        $ticket = TripTicket::with('vehicleRequest')->findOrFail($id);
        $doc = $ticket->document ?: $ticket->vehicleRequest?->document;

        if (!$doc || !Storage::disk('public')->exists($doc)) {
            abort(404, 'No signed document found for this trip ticket.');
        }

        $extension = strtolower(pathinfo($doc, PATHINFO_EXTENSION));
        $downloadFilename = 'CEO-Signed-Document-' . $ticket->ticket_number . '.' . $extension;

        return Storage::disk('public')->download($doc, $downloadFilename);
    }

    public function printTicket($id)
    {
        $ticket = TripTicket::with(['driver', 'vehicleRequest', 'vehicleRequests'])->findOrFail($id);

        $vehicleModel = '';
        $vehiclePlate = '';

        if ($ticket->vehicle) {
            $dbVehicle = \App\Models\Vehicle::where('plate_number', $ticket->vehicle)->first();
            if ($dbVehicle) {
                $vehicleModel = $dbVehicle->brand;
                $vehiclePlate = $dbVehicle->plate_number;
            } else {
                if (str_contains($ticket->vehicle, ' - ')) {
                    $parts = explode(' - ', $ticket->vehicle);
                    $vehicleModel = $parts[0] ?? '';
                    $vehiclePlate = $parts[1] ?? '';
                } else {
                    $vehicleModel = $ticket->vehicle;
                    $vehiclePlate = $ticket->vehicle;
                }
            }
        }

        return view('print.trip-ticket', compact('ticket', 'vehicleModel', 'vehiclePlate'));
    }

    public function printSlip($id)
    {
        $slip = \App\Models\WithdrawalSlip::with(['tripTicket.driver'])->findOrFail($id);
        
        $ticket = $slip->tripTicket;
        $driverName = $slip->driver_name ?: ($ticket?->driver?->name ?? '_______________');
        $vehicleModel = '';
        $vehiclePlate = '';

        $vehicleStr = $slip->vehicle_name ?: ($ticket ? $ticket->vehicle : '');
        if ($vehicleStr) {
            $dbVehicle = \App\Models\Vehicle::where('plate_number', $vehicleStr)->first();
            if ($dbVehicle) {
                $vehicleModel = $dbVehicle->brand;
                $vehiclePlate = $dbVehicle->plate_number;
            } else {
                if (preg_match('/^(.*?)\s*\((.*?)\)$/', $vehicleStr, $matches)) {
                    $vehicleModel = trim($matches[1]);
                    $vehiclePlate = trim($matches[2]);
                } elseif (str_contains($vehicleStr, ' - ')) {
                    $parts = explode(' - ', $vehicleStr);
                    $vehicleModel = $parts[0] ?? '';
                    $vehiclePlate = $parts[1] ?? '';
                } else {
                    $vehicleModel = $vehicleStr;
                    $vehiclePlate = $vehicleStr;
                }
            }
        }

        return view('print.withdrawal-slip', compact('slip', 'driverName', 'vehicleModel', 'vehiclePlate'));
    }

    public function printTravelOrder(Request $request, $id)
    {
        // Enforce role authorization: Only administrators can view, edit, and print Travel Orders
        $currentUser = auth('admin')->user() ?? auth('web')->user() ?? auth()->user();
        if (!$currentUser || (method_exists($currentUser, 'isAdmin') ? !$currentUser->isAdmin() : strtolower($currentUser->role ?? '') !== 'admin')) {
            abort(403, 'Unauthorized access. Only administrators are authorized to edit and print Travel Orders.');
        }

        $ticket = TripTicket::with(['driver', 'vehicleRequest', 'vehicleRequests'])->findOrFail($id);
        $type = $request->query('type', 'employee'); // employee or driver

        $vr = $ticket->vehicleRequest ?: $ticket->vehicleRequests->first();

        $name = '';
        $position = '';
        
        if ($type === 'driver') {
            $name = $ticket->driver?->name ?? '';
            $position = 'Administrative Assistant I / Driver';
        } else {
            $name = $vr?->employee_name ?? '';
            $position = $vr?->department ?? '';
        }

        $departure = '__________________';
        if ($vr && !empty($vr->date)) {
            $timeStr = !empty($vr->time) ? $vr->time : '00:00';
            try {
                $departure = \Carbon\Carbon::parse($vr->date . ' ' . $timeStr)->format('F d, Y h:i A');
            } catch (\Throwable $e) {
                $departure = $vr->date;
            }
        } elseif (!empty($ticket->departure_time)) {
            try {
                $departure = \Carbon\Carbon::parse($ticket->departure_time)->format('F d, Y h:i A');
            } catch (\Throwable $e) {}
        }

        $arrival = '__________________';
        if ($vr && !empty($vr->return_date)) {
            $returnTimeStr = !empty($vr->return_time) ? $vr->return_time : '00:00';
            try {
                $arrival = \Carbon\Carbon::parse($vr->return_date . ' ' . $returnTimeStr)->format('F d, Y h:i A');
            } catch (\Throwable $e) {
                $arrival = $vr->return_date;
            }
        } elseif (!empty($ticket->arrival_time)) {
            try {
                $arrival = \Carbon\Carbon::parse($ticket->arrival_time)->format('F d, Y h:i A');
            } catch (\Throwable $e) {}
        }
        
        $destination = $vr?->destination ?? '';
        $purpose = $vr?->purpose ?? '';

        $vehicleName = '';
        if ($ticket->vehicle) {
            $dbVehicle = \App\Models\Vehicle::where('plate_number', $ticket->vehicle)->first();
            $vehicleName = $dbVehicle ? "{$dbVehicle->brand} ({$dbVehicle->plate_number})" : $ticket->vehicle;
        }

        $seriesYear = date('Y');
        $seriesMonth = date('m');
        $seriesDay = date('d');

        if ($vr) {
            $reqDate = null;
            if (!empty($vr->created_at)) {
                $reqDate = $vr->created_at;
            } elseif (!empty($vr->date)) {
                try {
                    $reqDate = \Carbon\Carbon::parse($vr->date);
                } catch (\Throwable $e) {}
            }
            if ($reqDate) {
                $seriesYear = $reqDate->format('Y');
                $seriesMonth = $reqDate->format('m');
                $seriesDay = $reqDate->format('d');
            }
            if (!empty($vr->request_number)) {
                $cleanNum = preg_replace('/^VR-/', '', $vr->request_number);
                $parts = explode('-', $cleanNum);
                if (count($parts) >= 3) {
                    $seriesYear = $parts[0];
                    $seriesMonth = $parts[1];
                    $seriesDay = $parts[2];
                }
            }
        }

        $isOB = !str_contains(strtolower($purpose), 'official time');

        return view('print.travel-order', compact(
            'ticket',
            'type',
            'name',
            'position',
            'departure',
            'arrival',
            'destination',
            'purpose',
            'vehicleName',
            'seriesYear',
            'seriesMonth',
            'seriesDay',
            'isOB'
        ));
    }

    public function printAnalyticsReport(Request $request)
    {
        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');
        $status = $request->query('status');
        $department = $request->query('department');

        $query = VehicleRequest::with(['tripTicket.driver'])
            ->latest('date');

        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }
        if ($status) {
            $query->where('status', $status);
        }
        if ($department) {
            $query->where('department', $department);
        }

        $requests = $query->get();

        $totalRequests = $requests->count();
        $approvedCount = $requests->whereIn('status', ['approved', 'on_trip', 'completed'])->count();
        $rejectedCount = $requests->where('status', 'rejected')->count();
        $approvalRate = $totalRequests > 0 ? round(($approvedCount / $totalRequests) * 100, 1) : 0;
        
        // Count actual passengers from valid approved / on-trip / completed trips
        $totalPassengers = $requests->whereIn('status', ['approved', 'on_trip', 'completed'])->sum('number_of_passengers');

        // Fuel expenses
        $slipQuery = \App\Models\WithdrawalSlip::where('status', 'approved');
        if ($startDate) $slipQuery->whereDate('created_at', '>=', $startDate);
        if ($endDate) $slipQuery->whereDate('created_at', '<=', $endDate);
        $totalFuel = $slipQuery->sum('amount');
        $avgFuelPerTrip = $approvedCount > 0 ? round($totalFuel / $approvedCount, 2) : 0;

        // Breakdown by department (sorted by highest volume)
        $deptBreakdown = $requests->whereNotNull('department')
            ->where('department', '!=', '')
            ->groupBy('department')
            ->map->count()
            ->sortDesc();

        // Normalized breakdown by official fleet vehicles
        $allVehicles = \App\Models\Vehicle::all();
        $vehicleBreakdown = [];
        foreach ($allVehicles as $v) {
            $count = $requests->filter(function ($r) use ($v) {
                return !empty($r->vehicle) && str_contains(strtoupper($r->vehicle), strtoupper($v->plate_number));
            })->count();
            $vehicleBreakdown["{$v->brand} ({$v->plate_number})"] = $count;
        }

        $activeVehiclesCount = $allVehicles->count();

        return view('print.analytics-report', compact(
            'requests',
            'totalRequests',
            'approvedCount',
            'rejectedCount',
            'approvalRate',
            'totalPassengers',
            'totalFuel',
            'avgFuelPerTrip',
            'deptBreakdown',
            'vehicleBreakdown',
            'activeVehiclesCount',
            'startDate',
            'endDate',
            'status',
            'department'
        ));
    }

    public function printTripLogbook(\Illuminate\Http\Request $request)
    {
        $monthInput = $request->query('month');
        $yearInput = $request->query('year');

        if ($monthInput && str_contains($monthInput, '-')) {
            $parts = explode('-', $monthInput);
            $year = $parts[0];
            $month = str_pad($parts[1], 2, '0', STR_PAD_LEFT);
        } else {
            $year = $yearInput ?: now()->format('Y');
            $month = str_pad($monthInput ?: now()->format('m'), 2, '0', STR_PAD_LEFT);
        }
        $status = $request->query('status', 'all');

        $query = \App\Models\TripTicket::with(['vehicleRequest', 'vehicleRequests', 'driver'])
            ->where(function ($q) use ($year, $month) {
                $prefix = "TT-{$year}-{$month}-";
                $q->where('ticket_number', 'like', "{$prefix}%")
                  ->orWhere(function ($sub) use ($year, $month) {
                      $sub->whereYear('created_at', $year)
                          ->whereMonth('created_at', $month);
                  });
            });

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $trips = $query->orderBy('ticket_number', 'asc')->get();

        $monthName = \Carbon\Carbon::createFromDate((int)$year, (int)$month, 1)->format('F Y');
        $totalTrips = $trips->count();
        $completedTrips = $trips->where('status', 'completed')->count();
        $activeTrips = $trips->where('status', 'active')->count();

        $totalPassengers = 0;
        foreach ($trips as $trip) {
            $allReqs = $trip->all_vehicle_requests;
            foreach ($allReqs as $r) {
                $totalPassengers += ($r->number_of_passengers ?: 1);
            }
        }

        return view('print.trip-logbook', compact(
            'trips',
            'year',
            'month',
            'monthName',
            'status',
            'totalTrips',
            'completedTrips',
            'activeTrips',
            'totalPassengers'
        ));
    }

    public function exportTripLogbookCsv(\Illuminate\Http\Request $request)
    {
        $monthInput = $request->query('month');
        $yearInput = $request->query('year');

        if ($monthInput && str_contains($monthInput, '-')) {
            $parts = explode('-', $monthInput);
            $year = $parts[0];
            $month = str_pad($parts[1], 2, '0', STR_PAD_LEFT);
        } else {
            $year = $yearInput ?: now()->format('Y');
            $month = str_pad($monthInput ?: now()->format('m'), 2, '0', STR_PAD_LEFT);
        }
        $status = $request->query('status', 'all');

        $query = \App\Models\TripTicket::with(['vehicleRequest', 'vehicleRequests', 'driver'])
            ->where(function ($q) use ($year, $month) {
                $prefix = "TT-{$year}-{$month}-";
                $q->where('ticket_number', 'like', "{$prefix}%")
                  ->orWhere(function ($sub) use ($year, $month) {
                      $sub->whereYear('created_at', $year)
                          ->whereMonth('created_at', $month);
                  });
            });

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $trips = $query->orderBy('ticket_number', 'asc')->get();
        $filename = "CSU_Lallo_GSO_Trip_Logbook_{$year}_{$month}.xls";
        $periodLabel = \Carbon\Carbon::createFromDate((int) $year, (int) $month, 1)->format('F Y');

        return response()->streamDownload(function () use ($trips, $periodLabel) {
            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
            echo '<head>';
            echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
            echo '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Dispatch Logbook</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
            echo '<style>';
            echo 'table { border-collapse: collapse; font-family: Calibri, Arial, sans-serif; font-size: 11pt; }';
            echo 'th { background-color: #047857; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #1e293b; padding: 10px 14px; white-space: nowrap; }';
            echo 'td { border: 1px solid #cbd5e1; padding: 8px 12px; vertical-align: middle; }';
            echo '.title { font-size: 15pt; font-weight: bold; text-align: center; color: #047857; }';
            echo '.subtitle { font-size: 11pt; text-align: center; color: #475569; font-weight: bold; }';
            echo '.period { font-size: 11pt; text-align: center; color: #0f172a; font-weight: bold; }';
            echo '.center { text-align: center; }';
            echo '.status-completed { color: #047857; font-weight: bold; text-align: center; }';
            echo '.status-cancelled { color: #dc2626; font-weight: bold; text-align: center; }';
            echo '.status-ontrip { color: #0284c7; font-weight: bold; text-align: center; }';
            echo '.status-pending { color: #d97706; font-weight: bold; text-align: center; }';
            echo '.summary-label { font-weight: bold; background-color: #f1f5f9; text-align: right; border: 1px solid #cbd5e1; padding: 8px; }';
            echo '.summary-val { font-weight: bold; background-color: #f1f5f9; text-align: center; border: 1px solid #cbd5e1; padding: 8px; color: #047857; }';
            echo '</style>';
            echo '</head>';
            echo '<body>';
            echo '<table>';

            // Official Title Banner
            echo '<tr><td colspan="11" class="title" style="border:none; padding-top: 15px;">CAGAYAN STATE UNIVERSITY - LAL-LO CAMPUS</td></tr>';
            echo '<tr><td colspan="11" class="subtitle" style="border:none;">GENERAL SERVICES OFFICE (GSO) &bull; MOTORPOOL FLEET DISPATCH LOGBOOK</td></tr>';
            echo '<tr><td colspan="11" class="period" style="border:none; padding-bottom: 15px;">Official Dispatch Period: ' . htmlspecialchars($periodLabel) . '</td></tr>';
            echo '<tr><td colspan="11" style="border:none; height: 10px;"></td></tr>';

            // Table Header Row
            echo '<tr>';
            echo '<th style="width: 50px;">#</th>';
            echo '<th style="width: 150px;">TT Control Number</th>';
            echo '<th style="width: 120px;">Departure Date</th>';
            echo '<th style="width: 110px;">Departure Time</th>';
            echo '<th style="width: 110px;">Arrival Time</th>';
            echo '<th style="width: 280px;">Office / Department &amp; Passengers</th>';
            echo '<th style="width: 120px;">Passengers (Pax)</th>';
            echo '<th style="width: 250px;">Official Destination</th>';
            echo '<th style="width: 160px;">Vehicle Assigned</th>';
            echo '<th style="width: 160px;">Driver Assigned</th>';
            echo '<th style="width: 110px;">Trip Status</th>';
            echo '</tr>';

            $totalPassengers = 0;
            $completedCount = 0;
            $onTripCount = 0;
            $cancelledCount = 0;
            $pendingCount = 0;

            foreach ($trips as $idx => $trip) {
                $allReqs = $trip->all_vehicle_requests;
                $primaryReq = $trip->vehicleRequest;
                $depDate = $primaryReq?->date ? \Carbon\Carbon::parse($primaryReq->date)->format('M d, Y') : ($trip->created_at ? $trip->created_at->format('M d, Y') : '---');
                $depTime = $primaryReq?->time ? \Carbon\Carbon::parse($primaryReq->time)->format('g:i A') : '---';

                $arrTime = '---';
                if ($trip->gate_in_at) {
                    $arrTime = \Carbon\Carbon::parse($trip->gate_in_at)->timezone('Asia/Manila')->format('g:i A');
                } elseif ($trip->status === 'completed' && $trip->updated_at) {
                    $arrTime = \Carbon\Carbon::parse($trip->updated_at)->timezone('Asia/Manila')->format('g:i A');
                } elseif ($trip->status === 'active') {
                    $arrTime = 'On Trip';
                }

                $depts = $allReqs->pluck('department')->unique()->filter()->join('; ');
                $passengers = $allReqs->pluck('employee_name')->filter()->join('; ');
                $totalPax = 0;
                foreach ($allReqs as $r) {
                    $totalPax += ($r->number_of_passengers ?: 1);
                }
                $totalPassengers += $totalPax;

                $statusClass = match ($trip->status) {
                    'completed' => 'status-completed',
                    'active' => 'status-ontrip',
                    'cancelled' => 'status-cancelled',
                    'pending' => 'status-pending',
                    default => '',
                };

                $statusLabel = match ($trip->status) {
                    'active' => 'On Trip',
                    default => ucfirst($trip->status),
                };

                if ($trip->status === 'completed') $completedCount++;
                elseif ($trip->status === 'active') $onTripCount++;
                elseif ($trip->status === 'cancelled') $cancelledCount++;
                elseif ($trip->status === 'pending') $pendingCount++;

                $travelersInfo = ($depts ?: ($primaryReq?->department ?? 'General'));
                if ($passengers) {
                    $travelersInfo .= " &bull; {$passengers}";
                }

                $vehicleName = \App\Models\Vehicle::getVehicleName($trip->vehicle);
                $driverName = $trip->driver?->name ?? 'Unassigned';
                $dest = $primaryReq?->destination ?? '---';

                echo '<tr>';
                echo '<td class="center">' . ($idx + 1) . '</td>';
                echo '<td class="center" style="font-family: monospace; font-weight: bold;">' . htmlspecialchars($trip->formatted_ticket_number) . '</td>';
                echo '<td class="center">' . htmlspecialchars($depDate) . '</td>';
                echo '<td class="center">' . htmlspecialchars($depTime) . '</td>';
                echo '<td class="center">' . htmlspecialchars($arrTime) . '</td>';
                echo '<td>' . htmlspecialchars($travelersInfo) . '</td>';
                echo '<td class="center" style="font-weight: bold;">' . $totalPax . '</td>';
                echo '<td>' . htmlspecialchars($dest) . '</td>';
                echo '<td>' . htmlspecialchars($vehicleName) . '</td>';
                echo '<td>' . htmlspecialchars($driverName) . '</td>';
                echo '<td class="' . $statusClass . '">' . htmlspecialchars($statusLabel) . '</td>';
                echo '</tr>';
            }

            // Summary Footer Rows
            echo '<tr><td colspan="11" style="border:none; height: 10px;"></td></tr>';
            echo '<tr>';
            echo '<td colspan="6" class="summary-label">TOTAL PASSENGERS SERVED:</td>';
            echo '<td class="summary-val">' . $totalPassengers . ' pax</td>';
            echo '<td colspan="3" class="summary-label">DISPATCH SUMMARY:</td>';
            echo '<td class="summary-val">' . count($trips) . ' Tickets (' . $completedCount . ' Completed, ' . $onTripCount . ' On Trip)</td>';
            echo '</tr>';

            echo '</table>';
            echo '</body>';
            echo '</html>';
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
