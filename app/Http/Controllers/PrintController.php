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
        return view('print.vehicle-request', compact('request'));
    }

    public function printTicket($id)
    {
        $ticket = TripTicket::with(['driver', 'vehicleRequest'])->findOrFail($id);

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
        $vehicleModel = '';
        $vehiclePlate = '';

        if ($ticket && $ticket->vehicle) {
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

        return view('print.withdrawal-slip', compact('slip', 'vehicleModel', 'vehiclePlate'));
    }

    public function printTravelOrder(Request $request, $id)
    {
        $ticket = TripTicket::with(['driver', 'vehicleRequest'])->findOrFail($id);
        $type = $request->query('type', 'employee'); // employee or driver

        $name = '';
        $position = '';
        
        if ($type === 'driver') {
            $name = $ticket->driver?->name ?? '';
            $position = 'Administrative Assistant I / Driver';
        } else {
            $name = $ticket->vehicleRequest?->employee_name ?? '';
            $position = $ticket->vehicleRequest?->department ?? '';
        }

        $departure = \Carbon\Carbon::parse($ticket->vehicleRequest?->date . ' ' . $ticket->vehicleRequest?->time)->format('F d, Y h:i A');
        $arrival = $ticket->vehicleRequest?->return_date 
            ? \Carbon\Carbon::parse($ticket->vehicleRequest->return_date . ' ' . $ticket->vehicleRequest->return_time)->format('F d, Y h:i A')
            : '__________________';
        
        $destination = $ticket->vehicleRequest?->destination ?? '';
        $purpose = $ticket->vehicleRequest?->purpose ?? '';

        $vehicleName = '';
        if ($ticket->vehicle) {
            $dbVehicle = \App\Models\Vehicle::where('plate_number', $ticket->vehicle)->first();
            $vehicleName = $dbVehicle ? "{$dbVehicle->brand} ({$dbVehicle->plate_number})" : $ticket->vehicle;
        }

        return view('print.travel-order', compact('ticket', 'type', 'name', 'position', 'departure', 'arrival', 'destination', 'purpose', 'vehicleName'));
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

        // Fuel expenses
        $slipQuery = \App\Models\WithdrawalSlip::where('status', 'approved');
        if ($startDate) $slipQuery->whereDate('created_at', '>=', $startDate);
        if ($endDate) $slipQuery->whereDate('created_at', '<=', $endDate);
        $totalFuel = $slipQuery->sum('amount');
        $avgFuelPerTrip = $approvedCount > 0 ? round($totalFuel / $approvedCount, 2) : 0;

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
            'totalFuel',
            'avgFuelPerTrip',
            'deptBreakdown',
            'vehicleBreakdown',
            'startDate',
            'endDate',
            'status',
            'department'
        ));
    }
}
