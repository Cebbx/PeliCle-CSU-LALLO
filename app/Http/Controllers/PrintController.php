<?php

namespace App\Http\Controllers;

use App\Models\VehicleRequest;
use App\Models\TripTicket;
use Illuminate\Http\Request;

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
        ]);
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
        ]);
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
        $driverName = trim($slip->driver_name ?: ($ticket?->driver?->name ?? ''));
        if (str_replace('_', '', $driverName) === '') {
            $driverName = '';
        }

        $vehicleModel = '';
        $vehiclePlate = '';

        $vehicleStr = trim($slip->vehicle_name ?: ($ticket ? $ticket->vehicle : ''));
        if ($vehicleStr) {
            if (preg_match('/^(.*?)\s*\((.*?)\)$/', $vehicleStr, $matches)) {
                $rawModel = trim($matches[1]);
                $vehiclePlate = trim($matches[2]);
                $dbVehicle = \App\Models\Vehicle::where('plate_number', $vehiclePlate)->first();
                $vehicleModel = $dbVehicle ? $dbVehicle->brand : $rawModel;
            } elseif ($dbVehicle = \App\Models\Vehicle::where('plate_number', $vehicleStr)->first()) {
                $vehicleModel = $dbVehicle->brand;
                $vehiclePlate = $dbVehicle->plate_number;
            } elseif (str_contains($vehicleStr, ' - ')) {
                $parts = explode(' - ', $vehicleStr);
                $vehicleModel = trim($parts[0] ?? '');
                $vehiclePlate = trim($parts[1] ?? '');
            } else {
                $vehicleModel = $vehicleStr;
                $vehiclePlate = $vehicleStr;
            }
        }

        if ($vehicleModel) {
            $parts = explode(' ', $vehicleModel);
            if (count($parts) >= 2 && strcasecmp($parts[0], $parts[1]) === 0) {
                $vehicleModel = $parts[0];
            }
        }

        return view('print.withdrawal-slip', compact('slip', 'driverName', 'vehicleModel', 'vehiclePlate'));
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
        $totalPassengers = $requests->sum('number_of_passengers');

        // Breakdown by department
        $deptBreakdown = $requests->groupBy('department')->map->count();

        // Breakdown by vehicle
        $vehicleBreakdown = $requests->groupBy('vehicle')->map->count();

        return view('print.analytics-report', compact(
            'requests',
            'totalRequests',
            'approvedCount',
            'rejectedCount',
            'approvalRate',
            'totalPassengers',
            'deptBreakdown',
            'vehicleBreakdown',
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
        $filename = "CSU_Lallo_GSO_Trip_Logbook_{$year}_{$month}.csv";

        return response()->streamDownload(function () use ($trips) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel

            // Excel Column Headers
            fputcsv($handle, [
                'Row #',
                'TT Control Number',
                'Departure Date',
                'Departure Time',
                'Requesting Department(s)',
                'Passenger Names',
                'Total Passengers',
                'Destination',
                'Vehicle Assigned',
                'Driver Assigned',
                'Trip Status',
            ]);

            foreach ($trips as $idx => $trip) {
                $allReqs = $trip->all_vehicle_requests;
                $primaryReq = $trip->vehicleRequest;
                $depDate = $primaryReq?->date ? \Carbon\Carbon::parse($primaryReq->date)->format('Y-m-d') : ($trip->created_at ? $trip->created_at->format('Y-m-d') : 'N/A');
                $depTime = $primaryReq?->time ? \Carbon\Carbon::parse($primaryReq->time)->format('g:i A') : 'N/A';

                $depts = $allReqs->pluck('department')->unique()->filter()->join('; ');
                $passengers = $allReqs->pluck('employee_name')->filter()->join('; ');
                $totalPax = 0;
                foreach ($allReqs as $r) {
                    $totalPax += ($r->number_of_passengers ?: 1);
                }

                fputcsv($handle, [
                    $idx + 1,
                    $trip->formatted_ticket_number,
                    $depDate,
                    $depTime,
                    $depts ?: ($primaryReq?->department ?? 'General'),
                    $passengers ?: ($primaryReq?->employee_name ?? 'N/A'),
                    $totalPax,
                    $primaryReq?->destination ?? 'N/A',
                    \App\Models\Vehicle::getVehicleName($trip->vehicle),
                    $trip->driver?->name ?? 'Unassigned',
                    ucfirst($trip->status === 'active' ? 'On Trip' : $trip->status),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
