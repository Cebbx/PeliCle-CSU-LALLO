<?php

namespace App\Filament\Widgets;

use App\Models\TripTicket;
use Filament\Widgets\Widget;

class FleetCalendarWidget extends Widget
{
    protected string $view = 'filament.widgets.fleet-calendar';

    protected static ?int $sort = 0; // Render at the very top of the dashboard

    protected int | string | array $columnSpan = 'full'; // Full-width calendar grid layout

    protected function getViewData(): array
    {
        $tickets = TripTicket::with(['vehicleRequest', 'vehicleRequests', 'driver'])
            ->whereIn('status', ['pending', 'active', 'completed'])
            ->latest('updated_at')
            ->limit(50)
            ->get();

        $trips = [];
        $pendingCount = 0;
        $activeCount = 0;
        $completedCount = 0;

        foreach ($tickets as $ticket) {
            $req = $ticket->vehicleRequest ?? $ticket->vehicleRequests->first();
            $driverName = $ticket->driver?->name ?? 'Unassigned Driver';
            
            $status = $ticket->status;
            if ($status === 'active') {
                $activeCount++;
            } elseif ($status === 'pending') {
                $pendingCount++;
            } elseif ($status === 'completed') {
                $completedCount++;
            }

            $dateStr = $req?->date ?? ($ticket->created_at ? $ticket->created_at->format('Y-m-d') : date('Y-m-d'));
            $timeStr = $req?->time ?? '08:00:00';
            
            try {
                $parsedDate = \Carbon\Carbon::parse($dateStr . ' ' . $timeStr, 'Asia/Manila');
                $formattedDate = $parsedDate->format('M d, Y');
                $formattedTime = $parsedDate->format('h:i A');
                $dayOfWeek = $parsedDate->format('D');
                $monthShort = $parsedDate->format('M');
                $dayNum = $parsedDate->format('d');
                $isToday = $parsedDate->isToday();
            } catch (\Throwable $e) {
                $formattedDate = $dateStr;
                $formattedTime = $timeStr;
                $dayOfWeek = '';
                $monthShort = '';
                $dayNum = '';
                $isToday = false;
            }

            $destination = $req?->destination ?? 'Official Route / Campus Base';
            $purpose = $req?->purpose ?? 'University Official Business';
            $requestedBy = $req?->employee_name ?? ($req?->user?->name ?? 'CSU Employee');
            $dept = $req?->department ?? 'General Services';

            $viewUrl = \App\Filament\Resources\TripTickets\TripTicketResource::getUrl('view', ['record' => $ticket->id]);

            $trips[] = [
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'clean_ticket_number' => $ticket->formatted_ticket_number ?? $ticket->ticket_number,
                'status' => $status,
                'vehicle' => $ticket->vehicle ?: 'Assigned Vehicle',
                'driver' => $driverName,
                'destination' => $destination,
                'purpose' => $purpose,
                'requested_by' => $requestedBy,
                'department' => $dept,
                'date_str' => $dateStr,
                'formatted_date' => $formattedDate,
                'formatted_time' => $formattedTime,
                'day_of_week' => $dayOfWeek,
                'month_short' => $monthShort,
                'day_num' => $dayNum,
                'is_today' => $isToday,
                'url' => $viewUrl,
            ];
        }

        // Sort: Active first, then Pending, then Completed
        usort($trips, function ($a, $b) {
            $priority = ['active' => 1, 'pending' => 2, 'completed' => 3];
            $pA = $priority[$a['status']] ?? 4;
            $pB = $priority[$b['status']] ?? 4;
            if ($pA !== $pB) {
                return $pA <=> $pB;
            }
            return strcmp($b['date_str'], $a['date_str']);
        });

        // Compute current week days for the week bar
        $startOfWeek = \Carbon\Carbon::now('Asia/Manila')->startOfWeek();
        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $day = (clone $startOfWeek)->addDays($i);
            $dayDateStr = $day->format('Y-m-d');
            $hasTrip = collect($trips)->contains(fn ($t) => $t['date_str'] === $dayDateStr);
            $weekDays[] = [
                'name' => $day->format('D'),
                'num' => $day->format('d'),
                'date_str' => $dayDateStr,
                'is_today' => $day->isToday(),
                'has_trip' => $hasTrip,
            ];
        }

        return [
            'trips' => $trips,
            'weekDays' => $weekDays,
            'counts' => [
                'total' => count($trips),
                'active' => $activeCount,
                'pending' => $pendingCount,
                'completed' => $completedCount,
            ],
        ];
    }
}
