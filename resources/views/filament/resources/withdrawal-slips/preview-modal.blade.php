<div class="space-y-4">
    <div class="flex items-center justify-between pb-2 border-b border-gray-200 dark:border-gray-700">
        <div>
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Control Number</span>
            <p class="text-base font-extrabold text-primary-600 dark:text-primary-400 font-mono">{{ $record->slip_number }}</p>
        </div>
        <div class="text-right">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</span>
            <p class="text-xs font-bold uppercase">{{ ucfirst($record->status) }}</p>
        </div>
    </div>

    <div class="w-full bg-gray-100 dark:bg-gray-900 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-800 shadow-inner">
        <iframe 
            src="{{ route('withdrawal-slips.print', ['id' => $record->id, 'embed' => 1]) }}" 
            class="w-full h-[650px] border-0 rounded-lg"
            title="Withdrawal Slip Preview - {{ $record->slip_number }}"
        ></iframe>
    </div>
</div>
