<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-filament::icon
                    icon="heroicon-o-calendar"
                    class="h-5 w-5 text-gray-500"
                />
                <span>Fleet Booking & Travel Calendar</span>
            </div>
        </x-slot>

        <x-slot name="description">
            <div class="flex flex-wrap items-center gap-4 text-xs mt-1">
                <span class="flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded-full bg-[#d97706] block"></span> Pending Trip</span>
                <span class="flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded-full bg-[#2563eb] block"></span> Active (On Trip)</span>
                <span class="flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded-full bg-[#059669] block"></span> Completed Trip</span>
            </div>
        </x-slot>

        <!-- FullCalendar Stylesheets -->
        <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet" />
        
        <div class="w-full mt-4 bg-white dark:bg-gray-900 rounded-xl p-4 border border-gray-100 dark:border-gray-800">
            <div id="fleet-calendar" class="w-full text-sm min-h-[500px]" style="font-family: inherit;"></div>
        </div>

        <!-- FullCalendar JS Script -->
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
        <script>
            (function() {
                function initFleetCalendar() {
                    var calendarEl = document.getElementById('fleet-calendar');
                    if (!calendarEl || calendarEl.dataset.initialized === 'true' || typeof FullCalendar === 'undefined') return;
                    calendarEl.dataset.initialized = 'true';

                    var calendar = new FullCalendar.Calendar(calendarEl, {
                        initialView: 'dayGridMonth',
                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'dayGridMonth,timeGridWeek,timeGridDay'
                        },
                        events: @json($events),
                        eventClick: function(info) {
                            info.jsEvent.preventDefault();
                            if (info.event.url) {
                                window.location.href = info.event.url;
                            }
                        },
                        height: 'auto',
                        themeSystem: 'standard',
                        eventTimeFormat: {
                            hour: 'numeric',
                            minute: '2-digit',
                            meridiem: 'short'
                        }
                    });
                    
                    calendar.render();
                }

                if (document.readyState === 'complete' || document.readyState === 'interactive') {
                    setTimeout(initFleetCalendar, 50);
                } else {
                    document.addEventListener('DOMContentLoaded', initFleetCalendar);
                }
                document.addEventListener('livewire:navigated', function() {
                    var el = document.getElementById('fleet-calendar');
                    if (el) el.dataset.initialized = 'false';
                    setTimeout(initFleetCalendar, 50);
                });
            })();
        </script>
        
        <style>
            .fc {
                --fc-border-color: #f3f4f6;
                --fc-today-bg-color: rgba(217, 119, 6, 0.05);
            }
            .dark .fc {
                --fc-border-color: #374151;
                --fc-today-bg-color: rgba(251, 191, 36, 0.05);
            }
            .fc-col-header-cell-cushion, .fc-daygrid-day-number {
                color: inherit !important;
                text-decoration: none !important;
            }
            .fc-event {
                cursor: pointer;
                padding: 2px 6px;
                border-radius: 6px;
                border: none !important;
                font-weight: 500;
            }
        </style>
    </x-filament::section>
</x-filament-widgets::widget>
