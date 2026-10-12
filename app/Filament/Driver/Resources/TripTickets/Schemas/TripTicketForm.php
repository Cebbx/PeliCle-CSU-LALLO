<?php

namespace App\Filament\Driver\Resources\TripTickets\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class TripTicketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // 1. Emergency Aberya / Cancellation Banner (Prominently shown if cancelled)
                Section::make()
                    ->schema([
                        Placeholder::make('aberya_alert')
                            ->label('')
                            ->content(function ($record) {
                                if (!$record || !is_object($record) || $record->status !== 'cancelled' || empty($record->cancellation_reason)) {
                                    return '';
                                }

                                $reason = $record->cancellation_reason;
                                $isBreakdown = str_contains(strtolower($reason), 'breakdown') || str_contains(strtolower($reason), 'aberya') || str_contains(strtolower($reason), 'flat tire') || str_contains(strtolower($reason), 'engine');

                                $category = 'Vehicle Emergency / Aberya';
                                $location = null;
                                $passengers = null;
                                $comment = $reason;

                                if (preg_match('/Breakdown\s*\[(.*?)\]\s*-\s*(.*?)(?:\(Location:\s*(.*?)\s*\|\s*Passengers:\s*(.*?)\))?$/i', $reason, $m)) {
                                    $category = !empty($m[1]) ? trim($m[1]) : 'Breakdown';
                                    $comment = !empty($m[2]) ? trim($m[2]) : $reason;
                                    $location = !empty($m[3]) ? trim($m[3]) : null;
                                    $passengers = !empty($m[4]) ? trim($m[4]) : null;
                                }

                                $title = $isBreakdown ? '🚨 BIYAHE NA-ABORT / EMERGENCY ABERYA' : '⚠️ TRIP CANCELLATION NOTICE';
                                $sub = $isBreakdown 
                                    ? 'Nai-report ang aberya na ito habang nasa biyahe. Naka-rekord na ito sa system at naka-maintenance na ang sasakyan.'
                                    : 'Kinansela ang biyaheng ito at naka-log na sa official records.';

                                $html = "
                                <div style='background: linear-gradient(135deg, rgba(239, 68, 68, 0.08) 0%, rgba(220, 38, 38, 0.14) 100%); border: 1.5px solid rgba(239, 68, 68, 0.35); border-radius: 16px; padding: 18px 20px; box-shadow: 0 4px 15px -3px rgba(239, 68, 68, 0.15);'>
                                    <div style='display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;'>
                                        <div style='display: flex; align-items: center; gap: 12px;'>
                                            <div style='width: 40px; height: 40px; min-width: 40px; border-radius: 12px; background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.4); display: flex; align-items: center; justify-content: center; font-size: 20px;'>
                                                ⚠️
                                            </div>
                                            <div>
                                                <h3 style='margin: 0; font-size: 15px; font-weight: 800; color: #dc2626; letter-spacing: -0.01em;'>{$title}</h3>
                                                <p style='margin: 3px 0 0 0; font-size: 11.5px; color: #64748b;'>{$sub}</p>
                                            </div>
                                        </div>
                                        <span style='padding: 4px 12px; background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; border-radius: 9999px; font-size: 11.5px; font-weight: 800; text-transform: uppercase;'>
                                            ⚠️ {$category}
                                        </span>
                                    </div>
                                    
                                    <div style='margin-top: 14px; padding-top: 12px; border-top: 1px dashed rgba(239, 68, 68, 0.25); display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 10px; font-size: 12px;'>
                                        <div style='background: rgba(255,255,255,0.7); padding: 8px 12px; border-radius: 8px; border: 1px solid rgba(239, 68, 68, 0.15);'>
                                            <span style='display: block; font-size: 10px; text-transform: uppercase; font-weight: 700; color: #64748b; margin-bottom: 2px;'>💬 Paliwanag / Remarks</span>
                                            <span style='font-weight: 700; color: #0f172a;'>{$comment}</span>
                                        </div>
                                        " . ($location ? "
                                        <div style='background: rgba(255,255,255,0.7); padding: 8px 12px; border-radius: 8px; border: 1px solid rgba(239, 68, 68, 0.15);'>
                                            <span style='display: block; font-size: 10px; text-transform: uppercase; font-weight: 700; color: #64748b; margin-bottom: 2px;'>📍 Lokasyon</span>
                                            <span style='font-weight: 700; color: #0f172a;'>{$location}</span>
                                        </div>
                                        " : "") . "
                                        " . ($passengers ? "
                                        <div style='background: rgba(255,255,255,0.7); padding: 8px 12px; border-radius: 8px; border: 1px solid rgba(239, 68, 68, 0.15);'>
                                            <span style='display: block; font-size: 10px; text-transform: uppercase; font-weight: 700; color: #64748b; margin-bottom: 2px;'>👥 Kalagayan ng Pasahero</span>
                                            <span style='font-weight: 700; color: #059669;'>{$passengers}</span>
                                        </div>
                                        " : "") . "
                                    </div>
                                </div>
                                ";

                                return new HtmlString($html);
                            })
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => $record && is_object($record) && $record->status === 'cancelled' && !empty($record->cancellation_reason)),

                // 2. Main Assigned Trip Information
                Section::make('Assigned Trip Information')
                    ->description(fn ($record) => ($record && is_object($record)) ? "Trip Ticket: {$record->formatted_ticket_number}" : null)
                    ->components([
                        Grid::make(2)
                            ->components([
                                Placeholder::make('destination')
                                    ->label('Destination')
                                    ->content(function ($record) {
                                        if (!$record || !is_object($record)) return 'N/A';
                                        $dest = $record->vehicleRequest?->destination ?? 'N/A';
                                        return new HtmlString("<div style='font-size: 13.5px; font-weight: 700; color: #0284c7; display: flex; align-items: center; gap: 6px;'>📍 <span>{$dest}</span></div>");
                                    }),

                                Placeholder::make('status')
                                    ->label('Trip Status')
                                    ->content(function ($record) {
                                        if (!$record || !is_object($record)) return 'N/A';
                                        $st = strtolower($record->status ?? 'pending');
                                        $map = [
                                            'active' => ['bg' => '#dbeafe', 'text' => '#1d4ed8', 'border' => '#93c5fd', 'label' => '🚗 On Trip / En Route'],
                                            'completed' => ['bg' => '#dcfce7', 'text' => '#15803d', 'border' => '#86efac', 'label' => '✅ Completed'],
                                            'cancelled' => ['bg' => '#fee2e2', 'text' => '#b91c1c', 'border' => '#fca5a5', 'label' => '🛑 Cancelled / Aborted'],
                                            'pending' => ['bg' => '#fef3c7', 'text' => '#b45309', 'border' => '#fde68a', 'label' => '⏳ Pending Dispatch'],
                                        ];
                                        $cfg = $map[$st] ?? ['bg' => '#f1f5f9', 'text' => '#475569', 'border' => '#cbd5e1', 'label' => ucfirst($st)];
                                        return new HtmlString("<span style='display: inline-flex; align-items: center; padding: 4px 12px; border-radius: 9999px; font-size: 11.5px; font-weight: 800; background: {$cfg['bg']}; color: {$cfg['text']}; border: 1px solid {$cfg['border']};'>{$cfg['label']}</span>");
                                    }),

                                Placeholder::make('vehicle')
                                    ->label('Vehicle')
                                    ->content(function ($record) {
                                        if (!$record || !is_object($record) || !$record->vehicle) return 'N/A';
                                        $vName = \App\Models\Vehicle::getVehicleName($record->vehicle);
                                        $vPlate = \App\Models\Vehicle::getPlateNumber($record->vehicle);
                                        $plateHtml = ($vPlate && $vPlate !== $vName) ? "<span style='margin-left: 6px; padding: 1px 6px; font-size: 10.5px; font-weight: 700; background: #e2e8f0; color: #1e293b; border-radius: 4px;'>{$vPlate}</span>" : "";
                                        return new HtmlString("<div style='font-size: 13px; font-weight: 700;'>🚐 {$vName} {$plateHtml}</div>");
                                    }),

                                Placeholder::make('schedule')
                                    ->label('Travel Date & Time')
                                    ->content(function ($record) {
                                        if (!$record || !is_object($record) || !$record->vehicleRequest) return 'N/A';
                                        $d = $record->vehicleRequest->date ? \Carbon\Carbon::parse($record->vehicleRequest->date)->format('F d, Y') : 'N/A';
                                        $t = $record->vehicleRequest->time ? \Carbon\Carbon::parse($record->vehicleRequest->time)->format('h:i A') : '';
                                        return new HtmlString("<div style='font-size: 13px; font-weight: 600;'>📅 {$d} " . ($t ? "<span style='color: #64748b; font-size: 11.5px;'>({$t})</span>" : "") . "</div>");
                                    }),

                                Placeholder::make('passenger')
                                    ->label('Authorized Passenger(s)')
                                    ->content(function ($record) {
                                        if (!$record || !is_object($record)) return 'N/A';
                                        $req = $record->vehicleRequest;
                                        if (!$req) return 'N/A';
                                        $name = $req->employee_name ?? 'N/A';
                                        $dept = $req->department ? "({$req->department})" : "";
                                        $count = (int) ($req->number_of_passengers ?: 1);
                                        return new HtmlString("<div style='font-size: 13px; font-weight: 700;'>👤 {$name} <span style='font-size: 11px; color: #64748b; font-weight: 500;'>{$dept} &bull; {$count} pax</span></div>");
                                    }),

                                Placeholder::make('purpose')
                                    ->label('Purpose of Travel')
                                    ->content(fn ($record) => ($record && is_object($record)) ? ($record->vehicleRequest?->purpose ?? 'N/A') : 'N/A'),
                            ]),
                    ]),

                // 3. Trip Completion QR Code Section (Only active trips)
                Section::make('Trip Completion QR Code')
                    ->description('Show this QR code to the school guard to complete your trip upon return.')
                    ->components([
                        Placeholder::make('qr_code')
                            ->label('')
                            ->content(function ($record) {
                                if (!$record || !is_object($record) || !$record->ticket_number) {
                                    return 'N/A';
                                }

                                $completionUrl = route('trip-tickets.complete-via-qr', ['ticket_number' => $record->ticket_number]);
                                $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' . urlencode($completionUrl);

                                $html = "
                                <div class='flex flex-col items-center justify-center p-6 bg-slate-900/10 dark:bg-white/5 rounded-2xl border border-slate-200 dark:border-slate-800 w-full max-w-sm mx-auto gap-4'>
                                    <img src='{$qrCodeUrl}' alt='QR Code' class='w-48 h-48 rounded-xl shadow-md border-2 border-white dark:border-slate-700' />
                                    <div class='text-center'>
                                        <p class='text-xs text-slate-500 dark:text-slate-400 font-medium'>Ask the guard to scan this QR code with their smartphone camera to mark this trip as completed.</p>
                                    </div>
                                </div>
                                ";

                                return new HtmlString($html);
                            })
                    ])
                    ->visible(fn ($record) => $record && is_object($record) && $record->status === 'active'),
            ]);
    }
}
