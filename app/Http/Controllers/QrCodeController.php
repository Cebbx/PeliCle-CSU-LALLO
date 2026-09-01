<?php

namespace App\Http\Controllers;

use App\Models\TripTicket;
use App\Models\Driver;
use Illuminate\Http\Request;
use Carbon\Carbon;

class QrCodeController extends Controller
{
    public function completeTrip(Request $request, $ticketNumber)
    {
        $ticket = TripTicket::where('ticket_number', $ticketNumber)->first();

        if (!$ticket) {
            return $this->renderResponse(
                'error',
                'Trip Ticket Not Found',
                "We couldn't find any trip ticket with reference number: <strong>{$ticketNumber}</strong>.",
                null
            );
        }

        // Check if user is authenticated (Admin/Driver) or session is guard_verified or PIN provided
        $providedPin = $request->input('pin') ?? $request->query('pin');
        $isAuthorized = session('guard_verified', false) 
            || auth()->check() 
            || $providedPin === '1234';

        if (!$isAuthorized) {
            // If they submitted a wrong PIN
            $errorMsg = $request->isMethod('post') ? 'Incorrect Security PIN. Please enter 1234.' : null;
            return $this->renderPinPrompt($ticket, $errorMsg);
        }

        // Check current status
        if ($ticket->status === 'completed') {
            return $this->renderResponse(
                'info',
                'Trip Already Completed',
                "This trip ticket (<strong>{$ticketNumber}</strong>) was already marked as completed.",
                $ticket
            );
        }

        if ($ticket->status !== 'active') {
            return $this->renderResponse(
                'warning',
                'Trip Not Active',
                "This trip ticket (<strong>{$ticketNumber}</strong>) is currently in <strong>" . ucfirst($ticket->status) . "</strong> status. Only trips currently \"On Trip\" can be completed.",
                $ticket
            );
        }

        // Complete the trip ticket!
        $ticket->update(['status' => 'completed']);

        // Sync associated Vehicle Request
        if ($ticket->vehicleRequest) {
            $ticket->vehicleRequest->update(['status' => 'completed']);
        }

        if ($ticket->driver) {
            $ticket->driver->update(['status' => 'available']);
        }

        return $this->renderResponse(
            'success',
            'Gate Clearance Verified! Trip Completed',
            "Trip ticket <strong>{$ticketNumber}</strong> has been successfully completed. The driver and vehicle are now available.",
            $ticket
        );
    }

    private function renderPinPrompt(TripTicket $ticket, $errorMessage = null)
    {
        $driverName = e($ticket->driver?->name ?? 'N/A');
        $vehicleName = e($ticket->vehicle ?? 'N/A');
        $destination = e($ticket->vehicleRequest?->destination ?? 'N/A');
        $actionUrl = route('trip-tickets.complete-via-qr', ['ticket_number' => $ticket->ticket_number]);

        $errorAlert = $errorMessage 
            ? "<div class='p-3 bg-red-500/10 border border-red-500/20 text-red-400 text-xs rounded-xl mb-4 text-center font-bold'>{$errorMessage}</div>" 
            : '';

        $html = "
        <!DOCTYPE html>
        <html lang='en' class='h-full'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no'>
            <title>Gate Clearance Authorization - {$ticket->ticket_number}</title>
            <script src='https://cdn.tailwindcss.com'></script>
            <link href='https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap' rel='stylesheet'>
            <style>body { font-family: 'Outfit', sans-serif; }</style>
        </head>
        <body class='min-h-full flex items-center justify-center bg-gradient-to-tr from-gray-950 via-slate-900 to-gray-950 p-4 text-slate-100 antialiased'>
            <div class='w-full max-w-md bg-slate-900/60 backdrop-blur-2xl border border-slate-800 rounded-3xl p-6 sm:p-8 text-center shadow-2xl relative overflow-hidden'>
                <!-- Header Icon -->
                <div class='w-16 h-16 bg-amber-500/10 border border-amber-500/20 text-amber-400 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg'>
                    <svg class='w-8 h-8' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                        <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z' />
                    </svg>
                </div>

                <span class='inline-block text-[10px] uppercase font-mono font-bold tracking-widest text-amber-400 bg-amber-400/10 px-2.5 py-1 rounded-full border border-amber-400/20 mb-2'>
                    Gate Pass: {$ticket->ticket_number}
                </span>

                <h1 class='text-xl font-extrabold text-white mb-1'>Gate Clearance Pass</h1>
                <p class='text-xs text-slate-400 mb-5'>Enter the 4-digit Security PIN to complete and record trip arrival.</p>

                {$errorAlert}

                <!-- Trip Details Summary -->
                <div class='bg-slate-950/60 border border-slate-800/80 rounded-2xl p-4 text-left text-xs space-y-2.5 mb-5'>
                    <div class='flex justify-between'><span class='text-slate-500'>Driver:</span> <strong class='text-slate-200'>{$driverName}</strong></div>
                    <div class='flex justify-between'><span class='text-slate-500'>Vehicle:</span> <strong class='text-slate-200'>{$vehicleName}</strong></div>
                    <div class='flex justify-between items-start gap-3'><span class='text-slate-500 shrink-0'>Destination:</span> <strong class='text-slate-200 text-right'>{$destination}</strong></div>
                    <div class='flex justify-between items-center'><span class='text-slate-500'>Current Status:</span> <span class='px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-500/20 text-amber-300 border border-amber-500/30'>{$ticket->status}</span></div>
                </div>

                <!-- Form -->
                <form method='POST' action='{$actionUrl}' class='space-y-4'>
                    <input type='hidden' name='_token' value='" . csrf_token() . "'>
                    
                    <div>
                        <label class='block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2'>Security Guard PIN</label>
                        <input type='password' name='pin' value='1234' maxlength='6' required autofocus class='w-full text-center tracking-[0.4em] font-mono text-xl py-3 bg-slate-950 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-emerald-500 transition shadow-inner'>
                        <span class='text-[10px] text-slate-500 mt-1 block'>Default Campus Gate PIN: 1234</span>
                    </div>

                    <button type='submit' class='w-full py-3.5 px-6 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-extrabold text-xs uppercase tracking-wider rounded-xl transition shadow-lg shadow-emerald-950/30 active:scale-95 cursor-pointer flex items-center justify-center gap-2'>
                        <svg class='w-4 h-4' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2.5' d='M5 13l4 4L19 7'/></svg>
                        Verify & Complete Trip
                    </button>
                </form>

                <div class='mt-6 pt-4 border-t border-slate-800/60 flex items-center justify-between text-[10px] text-slate-500'>
                    <span>CSU-SM Security Clearance</span>
                    <a href='/guard/scanner' class='text-emerald-400 hover:underline font-semibold'>Open Scanner Camera &rarr;</a>
                </div>
            </div>
        </body>
        </html>
        ";

        return response($html);
    }

    private function renderResponse($type, $title, $message, ?TripTicket $ticket)
    {
        $bgColor = 'from-gray-900 to-slate-950';
        $cardBg = 'bg-slate-900/40 backdrop-blur-xl border border-slate-800';
        $iconHtml = '';
        $themeColor = 'blue';

        if ($type === 'success') {
            $themeColor = 'emerald';
            $iconHtml = '<div class="w-20 h-20 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-full flex items-center justify-center mx-auto mb-6 shadow-[0_0_50px_rgba(16,185,129,0.15)] animate-bounce">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>';
        } elseif ($type === 'error') {
            $themeColor = 'red';
            $iconHtml = '<div class="w-20 h-20 bg-red-500/10 border border-red-500/20 text-red-400 rounded-full flex items-center justify-center mx-auto mb-6 shadow-[0_0_50px_rgba(239,68,68,0.15)]">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>';
        } elseif ($type === 'warning') {
            $themeColor = 'amber';
            $iconHtml = '<div class="w-20 h-20 bg-amber-500/10 border border-amber-500/20 text-amber-400 rounded-full flex items-center justify-center mx-auto mb-6 shadow-[0_0_50px_rgba(245,158,11,0.15)]">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>';
        } else {
            $themeColor = 'blue';
            $iconHtml = '<div class="w-20 h-20 bg-blue-500/10 border border-blue-500/20 text-blue-400 rounded-full flex items-center justify-center mx-auto mb-6 shadow-[0_0_50px_rgba(59,130,246,0.15)]">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>';
        }

        $detailsHtml = '';
        if ($ticket) {
            $driverName = $ticket->driver?->name ?? 'N/A';
            $vehicleName = $ticket->vehicle ?? 'N/A';
            $destination = $ticket->vehicleRequest?->destination ?? 'N/A';
            $completedAt = Carbon::now('Asia/Manila')->format('F d, Y h:i A');

            $detailsHtml = "
            <div class='mt-8 pt-6 border-t border-slate-800/60 text-left text-xs text-slate-400 space-y-3.5'>
                <div class='flex justify-between items-center'><span class='text-slate-500'>Driver:</span> <strong class='text-slate-200'>{$driverName}</strong></div>
                <div class='flex justify-between items-center'><span class='text-slate-500'>Vehicle:</span> <strong class='text-slate-200'>{$vehicleName}</strong></div>
                <div class='flex justify-between items-start gap-4'><span class='text-slate-500 shrink-0'>Destination:</span> <strong class='text-slate-200 text-right'>{$destination}</strong></div>
                <div class='flex justify-between items-center'><span class='text-slate-500'>Completed:</span> <strong class='text-slate-200'>{$completedAt}</strong></div>
            </div>";
        }

        $html = "
        <!DOCTYPE html>
        <html lang='en' class='h-full'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>{$title}</title>
            <script src='https://cdn.tailwindcss.com'></script>
            <link href='https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap' rel='stylesheet'>
            <style>
                body {
                    font-family: 'Outfit', sans-serif;
                }
            </style>
        </head>
        <body class='h-full flex items-center justify-center bg-gradient-to-tr {$bgColor} p-4 text-slate-100 antialiased'>
            <div class='w-full max-w-md {$cardBg} rounded-3xl p-8 text-center shadow-2xl relative overflow-hidden transition-all duration-300 hover:scale-[1.01]'>
                <!-- Glow Effect -->
                <div class='absolute -top-24 -left-24 w-48 h-48 bg-{$themeColor}-500/10 rounded-full blur-3xl pointer-events-none'></div>
                
                {$iconHtml}
                
                <h1 class='text-2xl font-extrabold tracking-tight text-white mb-3'>{$title}</h1>
                <p class='text-sm text-slate-300 leading-relaxed'>{$message}</p>
                
                {$detailsHtml}
                
                <div class='mt-8 flex flex-col gap-2.5'>
                    <a href='/guard/scanner' class='w-full inline-flex items-center justify-center bg-slate-950 border border-slate-800/80 hover:border-emerald-500/40 text-slate-300 hover:text-emerald-400 font-bold py-3 px-6 rounded-2xl text-xs uppercase tracking-wider transition-all shadow-lg hover:shadow-emerald-950/20'>
                        Scan Next Vehicle
                    </a>
                </div>

                <div class='mt-6 shrink-0'>
                    <span class='inline-block text-[10px] uppercase tracking-widest text-slate-500 font-semibold'>PeliCle Trip Management</span>
                </div>
            </div>
        </body>
        </html>";

        return response($html);
    }

    public function scannerPage()
    {
        $isVerified = session('guard_verified', false);
        return view('guard.scanner', compact('isVerified'));
    }

    public function verifyPin(Request $request)
    {
        $pin = $request->input('pin');
        if ($pin === '1234') {
            session(['guard_verified' => true]);
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'message' => 'Incorrect PIN code! Please try again.']);
    }
}
