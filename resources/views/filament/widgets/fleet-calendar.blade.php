<div x-data="{ 
    activeTab: 'all',
    selectedDate: ''
}" class="w-full">
    <div class="fleet-calendar-card" style="background: #0f172a; border: 1px solid #1e293b; border-radius: 14px; padding: 20px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2); font-family: 'Outfit', sans-serif;">
        
        <!-- Header with Title, Status Pills, and Filter Tabs -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 14px; padding-bottom: 16px; border-bottom: 1px solid rgba(255, 255, 255, 0.08);">
            <div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 34px; height: 34px; border-radius: 8px; background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3); display: flex; align-items: center; justify-content: center; font-size: 16px;">
                        🗓️
                    </div>
                    <div>
                        <h2 style="font-size: 16px; font-weight: 800; color: #ffffff; margin: 0; line-height: 1.2;">
                            Fleet Booking &amp; Travel Calendar
                        </h2>
                        <p style="font-size: 12px; color: #94a3b8; margin: 2px 0 0 0;">
                            Real-time schedule of campus vehicle departures, on-trip fleets, and official travel bookings
                        </p>
                    </div>
                </div>

                <!-- Clean Status Legend -->
                <div style="display: flex; align-items: center; gap: 16px; margin-top: 10px; font-size: 11px; color: #cbd5e1; flex-wrap: wrap;">
                    <span style="display: inline-flex; align-items: center; gap: 6px;">
                        <span style="width: 8px; height: 8px; border-radius: 50%; background: #f59e0b; display: inline-block;"></span>
                        Pending Trip (Scheduled)
                    </span>
                    <span style="display: inline-flex; align-items: center; gap: 6px;">
                        <span style="width: 8px; height: 8px; border-radius: 50%; background: #3b82f6; display: inline-block;"></span>
                        Active (On Trip / En Route)
                    </span>
                    <span style="display: inline-flex; align-items: center; gap: 6px;">
                        <span style="width: 8px; height: 8px; border-radius: 50%; background: #10b981; display: inline-block;"></span>
                        Completed Trip
                    </span>
                </div>
            </div>

            <!-- Filter Buttons -->
            <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                <button type="button" 
                        @click="activeTab = 'all'; selectedDate = ''" 
                        :style="activeTab === 'all' && selectedDate === '' ? 'background: #3b82f6; color: #ffffff; border-color: #3b82f6;' : 'background: #1e293b; color: #94a3b8; border-color: #334155;'"
                        style="padding: 6px 12px; border-radius: 8px; font-size: 11.5px; font-weight: 700; border: 1px solid; cursor: pointer; transition: all 0.2s;">
                    All Trips ({{ $counts['total'] }})
                </button>
                <button type="button" 
                        @click="activeTab = 'pending'" 
                        :style="activeTab === 'pending' ? 'background: #d97706; color: #ffffff; border-color: #d97706;' : 'background: #1e293b; color: #94a3b8; border-color: #334155;'"
                        style="padding: 6px 12px; border-radius: 8px; font-size: 11.5px; font-weight: 700; border: 1px solid; cursor: pointer; transition: all 0.2s;">
                    🟡 Pending ({{ $counts['pending'] }})
                </button>
                <button type="button" 
                        @click="activeTab = 'active'" 
                        :style="activeTab === 'active' ? 'background: #2563eb; color: #ffffff; border-color: #2563eb;' : 'background: #1e293b; color: #94a3b8; border-color: #334155;'"
                        style="padding: 6px 12px; border-radius: 8px; font-size: 11.5px; font-weight: 700; border: 1px solid; cursor: pointer; transition: all 0.2s;">
                    🔵 Active ({{ $counts['active'] }})
                </button>
                <button type="button" 
                        @click="activeTab = 'completed'" 
                        :style="activeTab === 'completed' ? 'background: #059669; color: #ffffff; border-color: #059669;' : 'background: #1e293b; color: #94a3b8; border-color: #334155;'"
                        style="padding: 6px 12px; border-radius: 8px; font-size: 11.5px; font-weight: 700; border: 1px solid; cursor: pointer; transition: all 0.2s;">
                    🟢 Completed ({{ $counts['completed'] }})
                </button>
            </div>
        </div>

        <!-- Weekly Schedule Strip -->
        <div style="margin-top: 14px; padding: 10px 14px; background: #0b1120; border: 1px solid rgba(255, 255, 255, 0.06); border-radius: 10px; display: flex; align-items: center; justify-content: space-between; overflow-x: auto; gap: 8px;">
            <div style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; padding-right: 8px; white-space: nowrap;">
                This Week:
            </div>
            <div style="display: flex; align-items: center; gap: 8px; flex: 1; justify-content: space-around;">
                @foreach($weekDays as $wDay)
                    <button type="button"
                            @click="selectedDate = (selectedDate === '{{ $wDay['date_str'] }}' ? '' : '{{ $wDay['date_str'] }}')"
                            :style="selectedDate === '{{ $wDay['date_str'] }}' 
                                ? 'background: #3b82f6; border-color: #60a5fa; color: #ffffff;' 
                                : '{{ $wDay['is_today'] ? 'background: rgba(245, 158, 11, 0.15); border-color: rgba(245, 158, 11, 0.4); color: #f59e0b;' : 'background: #111827; border-color: #1f293d; color: #94a3b8;' }}'"
                            style="padding: 6px 12px; border: 1px solid; border-radius: 8px; cursor: pointer; display: flex; flex-direction: column; align-items: center; min-width: 52px; transition: all 0.2s;">
                        <span style="font-size: 9.5px; font-weight: 700; text-transform: uppercase;">{{ $wDay['name'] }}</span>
                        <span style="font-size: 14px; font-weight: 800; margin-top: 1px;">{{ $wDay['num'] }}</span>
                        @if($wDay['has_trip'])
                            <span style="width: 4px; height: 4px; border-radius: 50%; background: #38bdf8; margin-top: 2px;"></span>
                        @else
                            <span style="width: 4px; height: 4px; opacity: 0; margin-top: 2px;"></span>
                        @endif
                    </button>
                @endforeach
            </div>
            <button type="button" 
                    x-show="selectedDate !== ''" 
                    @click="selectedDate = ''" 
                    style="font-size: 11px; color: #38bdf8; background: transparent; border: none; cursor: pointer; text-decoration: underline; white-space: nowrap; padding-left: 8px;">
                Clear Date Filter
            </button>
        </div>

        <!-- Schedule Items List -->
        <div style="margin-top: 16px; display: flex; flex-direction: column; gap: 10px; max-height: 480px; overflow-y: auto; padding-right: 4px;">
            @forelse($trips as $trip)
                <div x-show="(activeTab === 'all' || activeTab === '{{ $trip['status'] }}') && (selectedDate === '' || selectedDate === '{{ $trip['date_str'] }}')"
                     class="fleet-trip-row"
                     style="background: #111827; border: 1px solid #1f293d; border-radius: 10px; padding: 12px 16px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; transition: all 0.15s;"
                     onmouseover="this.style.background='#162238'; this.style.borderColor='#334155';"
                     onmouseout="this.style.background='#111827'; this.style.borderColor='#1f293d';">
                    
                    <!-- Left: Date & Time Box -->
                    <div style="display: flex; align-items: center; gap: 14px; min-width: 140px;">
                        <div style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; padding: 6px 10px; text-align: center; min-width: 58px;">
                            <span style="display: block; font-size: 9.5px; font-weight: 800; color: #94a3b8; text-transform: uppercase;">{{ $trip['month_short'] ?: 'DATE' }}</span>
                            <span style="display: block; font-size: 17px; font-weight: 800; color: #ffffff; line-height: 1.1;">{{ $trip['day_num'] ?: '--' }}</span>
                            <span style="display: block; font-size: 9px; font-weight: 700; color: #38bdf8;">{{ $trip['day_of_week'] }}</span>
                        </div>
                        <div>
                            <div style="font-size: 12.5px; font-weight: 700; color: #f1f5f9; display: flex; align-items: center; gap: 6px;">
                                <span>⏰ {{ $trip['formatted_time'] }}</span>
                                @if($trip['is_today'])
                                    <span style="background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.4); font-size: 9px; font-weight: 800; padding: 1px 6px; border-radius: 4px;">TODAY</span>
                                @endif
                            </div>
                            <div style="font-size: 11px; color: #94a3b8; margin-top: 2px;">
                                {{ $trip['clean_ticket_number'] }}
                            </div>
                        </div>
                    </div>

                    <!-- Center: Trip Details (Vehicle, Driver, Destination) -->
                    <div style="flex: 1; min-width: 220px;">
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <span style="font-size: 13.5px; font-weight: 800; color: #ffffff;">
                                🚐 {{ $trip['vehicle'] }}
                            </span>
                        </div>
                        <div style="font-size: 12px; color: #cbd5e1; margin-top: 3px; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                            <strong style="color: #38bdf8;">📍 {{ $trip['destination'] }}</strong>
                            <span style="color: #64748b;">&bull;</span>
                            <span style="color: #94a3b8;">{{ $trip['purpose'] }}</span>
                        </div>
                        <div style="font-size: 11px; color: #64748b; margin-top: 3px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                            <span>👤 Driver: <strong style="color: #cbd5e1;">{{ $trip['driver'] }}</strong></span>
                            <span>&bull;</span>
                            <span>Requested by: <span style="color: #94a3b8;">{{ $trip['requested_by'] }} ({{ $trip['department'] }})</span></span>
                        </div>
                    </div>

                    <!-- Right: Status Badge & Open Button -->
                    <div style="display: flex; align-items: center; gap: 12px; flex-shrink: 0;">
                        @if($trip['status'] === 'active')
                            <span style="background: rgba(37, 99, 235, 0.2); color: #60a5fa; border: 1px solid rgba(37, 99, 235, 0.4); font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 9999px; display: inline-flex; align-items: center; gap: 5px;">
                                <span style="width: 6px; height: 6px; border-radius: 50%; background: #3b82f6;"></span>
                                En Route (Active)
                            </span>
                        @elseif($trip['status'] === 'pending')
                            <span style="background: rgba(217, 119, 6, 0.2); color: #fbbf24; border: 1px solid rgba(217, 119, 6, 0.4); font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 9999px; display: inline-flex; align-items: center; gap: 5px;">
                                <span style="width: 6px; height: 6px; border-radius: 50%; background: #f59e0b;"></span>
                                Scheduled (Pending)
                            </span>
                        @else
                            <span style="background: rgba(5, 150, 105, 0.2); color: #34d399; border: 1px solid rgba(5, 150, 105, 0.4); font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 9999px; display: inline-flex; align-items: center; gap: 5px;">
                                <span style="width: 6px; height: 6px; border-radius: 50%; background: #10b981;"></span>
                                Completed
                            </span>
                        @endif

                        <a href="{{ $trip['url'] }}" 
                           style="background: #1e293b; color: #38bdf8; border: 1px solid #334155; padding: 6px 12px; border-radius: 8px; font-size: 11.5px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; transition: all 0.2s;"
                           onmouseover="this.style.background='#38bdf8'; this.style.color='#0f172a';"
                           onmouseout="this.style.background='#1e293b'; this.style.color='#38bdf8';">
                            View Ticket &rarr;
                        </a>
                    </div>

                </div>
            @empty
                <div style="padding: 36px 16px; text-align: center; color: #64748b;">
                    <div style="font-size: 28px; margin-bottom: 8px;">🗓️</div>
                    <div style="font-size: 13.5px; font-weight: 700; color: #cbd5e1;">No Trip Tickets Found</div>
                    <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
                        Approved vehicle requests and generated trip tickets will be listed here automatically.
                    </div>
                </div>
            @endforelse
        </div>

    </div>
</div>
