<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('files', function () {
            return new \App\Support\WindowsSafeFilesystem;
        });

        $this->app->singleton(\Illuminate\Filesystem\Filesystem::class, function ($app) {
            return $app['files'];
        });

        $this->app->bind(
            \Filament\Auth\Http\Controllers\LogoutController::class,
            \App\Http\Controllers\Auth\PanelLogoutController::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Dynamically override APP_URL and scheme for asset/route generation when accessed via tunnel
        $host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? null;
        if ($host) {
            $host = trim(explode(',', $host)[0]);
            $proto = 'http';
            if (
                (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ||
                (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
            ) {
                $proto = 'https';
            }
            $currentUrl = $proto . '://' . $host;
            config(['app.url' => $currentUrl]);
            \Illuminate\Support\Facades\URL::forceRootUrl($currentUrl);
            if ($proto === 'https') {
                \Illuminate\Support\Facades\URL::forceScheme('https');
            }
        }

        $this->configureDefaults();

        // Real-time trip status activation based on travel date and time
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('trip_tickets')) {
                // 1. Activate trips with uploaded document whose departure time has arrived
                $pendingReadyTrips = \App\Models\TripTicket::where('status', 'pending')
                    ->where(function ($query) {
                        $query->whereNotNull('document')
                            ->orWhereHas('vehicleRequests', function ($q) {
                                $q->whereNotNull('document');
                            })
                            ->orWhereHas('vehicleRequest', function ($q) {
                                $q->whereNotNull('document');
                            });
                    })
                    ->with(['vehicleRequest', 'vehicleRequests', 'driver'])
                    ->get();

                $now = \Illuminate\Support\Carbon::now('Asia/Manila');

                foreach ($pendingReadyTrips as $trip) {
                    $primaryReq = $trip->vehicleRequest ?? $trip->vehicleRequests->first();
                    if (!$primaryReq) continue;

                    $depDate = $primaryReq->date ?? ($trip->created_at ? $trip->created_at->format('Y-m-d') : $now->format('Y-m-d'));
                    $depTime = $primaryReq->time ?? '00:00:00';
                    $tripDateTime = \Illuminate\Support\Carbon::parse("{$depDate} {$depTime}", 'Asia/Manila');

                    // Check if departure time has arrived AND is within reasonable activation window (within past 24 hours)
                    if ($now->greaterThanOrEqualTo($tripDateTime) && $now->diffInHours($tripDateTime, false) >= -24) {
                        // Activate trip ticket!
                        $trip->status = 'active';
                        if (!$trip->document) {
                            $doc = $trip->vehicleRequests->pluck('document')->filter()->first() ?? $primaryReq->document;
                            $trip->document = $doc;
                        }
                        $trip->save(); // This triggers model saving/saved hooks, updating driver status to 'on_trip' and syncing all requests!
                    }
                }

                // 2. Auto-decline pending trips with NO document after scheduled departure time has passed (past 30 mins)
                $expiredTrips = \App\Models\TripTicket::where('status', 'pending')
                    ->whereNull('document')
                    ->whereDoesntHave('vehicleRequests', function ($q) {
                        $q->whereNotNull('document');
                    })
                    ->whereDoesntHave('vehicleRequest', function ($q) {
                        $q->whereNotNull('document');
                    })
                    ->with(['vehicleRequest', 'vehicleRequests', 'driver'])
                    ->get();

                foreach ($expiredTrips as $trip) {
                    $primaryReq = $trip->vehicleRequest ?? $trip->vehicleRequests->first();
                    if (!$primaryReq) continue;

                    $depDate = $primaryReq->date ?? ($trip->created_at ? $trip->created_at->format('Y-m-d') : $now->format('Y-m-d'));
                    $depTime = $primaryReq->time ?? '00:00:00';
                    $tripDateTime = \Illuminate\Support\Carbon::parse("{$depDate} {$depTime}", 'Asia/Manila');

                    // If current time is over 24 hours past scheduled departure time without document
                    if ($now->diffInHours($tripDateTime, false) < -24) {
                        $formattedSchedule = $tripDateTime->format('M d, Y h:i A');
                        $autoReason = "Auto-declined: Scheduled departure time ({$formattedSchedule}) passed by over 24 hours without uploaded CEO signed approval document.";

                        // Cancel Trip Ticket quietly to prevent unintended recursive events
                        $trip->status = 'cancelled';
                        $trip->cancellation_reason = $autoReason;
                        $trip->saveQuietly();

                        // Reject linked Vehicle Request(s) quietly
                        $allReqs = $trip->vehicleRequests()->get();
                        if ($allReqs->isEmpty() && $trip->vehicleRequest) {
                            $allReqs = collect([$trip->vehicleRequest]);
                        }
                        foreach ($allReqs as $r) {
                            $r->status = 'rejected';
                            $r->rejection_reason = $autoReason;
                            $r->saveQuietly();
                        }

                        // Release Driver status manually
                        if ($trip->driver_id) {
                            $driver = \App\Models\Driver::find($trip->driver_id);
                            if ($driver) {
                                $driver->update(['status' => 'available']);
                            }
                            if (method_exists($trip, 'sendCancellationSms')) {
                                $trip->sendCancellationSms("Trip schedule lapsed ({$formattedSchedule}) without uploaded CEO signed document.");
                            }
                        }

                        // Release Vehicle status
                        \App\Models\TripTicket::syncVehicleStatus($trip->vehicle);

                        // Log to ActivityLog
                        \App\Models\ActivityLog::create([
                            'user_id' => null,
                            'user_name' => 'System',
                            'action' => 'Auto-Declined Request',
                            'model_type' => \App\Models\TripTicket::class,
                            'model_id' => $trip->id,
                            'details' => "System automatically declined request {$primaryReq->request_number} and cancelled ticket {$trip->ticket_number} (Departure time {$formattedSchedule} passed by 30+ minutes without CEO signature upload).",
                            'ip_address' => '127.0.0.1',
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Silence exceptions during database setup/migrations
        }

        // Register PWA Manifest, iOS Meta Tags, and Service Worker in Filament Head
        try {
            \Filament\Support\Facades\FilamentView::registerRenderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn (): string => '
                    <link rel="manifest" href="/manifest.json">
                    <meta name="theme-color" content="#1e3a8a">
                    <meta name="apple-mobile-web-app-capable" content="yes">
                    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
                    <link rel="apple-touch-icon" href="/icons/icon-192x192.png">
                    <script>
                        if ("serviceWorker" in navigator) {
                            window.addEventListener("load", function() {
                                navigator.serviceWorker.register("/sw.js").then(function(reg) {
                                    if (reg) reg.update();
                                }).catch(function(err) {});
                            });
                        }

                        // Mobile bfcache bypass: force fresh reload when user presses Back/Forward
                        window.addEventListener("pageshow", function(event) {
                            if (event.persisted) {
                                window.location.reload();
                            }
                        });

                        // Auto-refresh when mobile phone screen turns on or user switches back to browser
                        document.addEventListener("visibilitychange", function() {
                            if (document.visibilityState === "visible" && window.Livewire) {
                                try {
                                    window.Livewire.dispatch("$refresh");
                                } catch (e) {}
                            }
                        });

                        // Auto-refresh on window focus (app resume)
                        window.addEventListener("focus", function() {
                            if (window.Livewire) {
                                try {
                                    window.Livewire.dispatch("$refresh");
                                } catch (e) {}
                            }
                        });
                    </script>
                '
            );
        } catch (\Throwable $e) {
            // Silence if Filament is not fully loaded in CLI
        }
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
