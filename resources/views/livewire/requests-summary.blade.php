<x-pulse::card :cols="$cols" :rows="$rows" :class="$class">
    <x-pulse::card-header
        name="Requests Summary"
        x-bind:title="`Global Time: {{ number_format($allTime) }}ms; Global run at: ${formatDate('{{ $allRunAt }}')}; Key Time: {{ number_format($keyTime) }}ms; Key run at: ${formatDate('{{ $keyRunAt }}')};`"
        details="past {{ $this->periodForHumans() }}"
    >
        <x-slot:icon>
            <x-pulse::icons.cursor-arrow-rays />
        </x-slot:icon>
    </x-pulse::card-header>

    <x-pulse::scroll :expand="$expand" wire:poll.5s="">
        @if ($totalRequests->total === 0 && $totalRequests->success === 0)
            <x-pulse::no-results />
        @else
            <div class="flex flex-col gap-6">
                <div class="grid grid-cols-3 gap-3 text-center">
                    <div class="flex flex-col justify-center">
                        <span class="text-xl uppercase font-bold text-gray-700 dark:text-gray-300 tabular-nums">
                            @if ($config['sample_rate'] < 1)
                                <span title="Sample rate: {{ $config['sample_rate'] }}, Raw value: {{ number_format($totalRequests->total) }}">~{{ number_format($totalRequests->total * (1 / $config['sample_rate'])) }}</span>
                            @else
                                {{ number_format($totalRequests->total) }}
                            @endif
                        </span>
                        <span class="text-xs uppercase font-bold text-gray-500 dark:text-gray-400">
                            Total
                        </span>
                    </div>
                    <div class="flex flex-col justify-center">
                        <span class="text-xl uppercase font-bold text-gray-700 dark:text-gray-300 tabular-nums">
                            @if ($config['sample_rate'] < 1)
                                <span title="Sample rate: {{ $config['sample_rate'] }}, Raw value: {{ number_format($totalRequests->success) }}">~{{ number_format(($totalRequests->success) * (1 / $config['sample_rate'])) }}</span>
                            @else
                                {{ number_format($totalRequests->success) }}
                            @endif
                        </span>
                        <span class="text-xs uppercase font-bold text-gray-500 dark:text-gray-400">
                            Successful
                        </span>
                    </div>
                    <div class="flex flex-col justify-center">
                        <span class="text-xl uppercase font-bold text-gray-700 dark:text-gray-300 tabular-nums">
                            {{ ((int) ($totalRequests->total / ($totalRequests->total + $totalRequests->success) * 10000)) / 100 }}%
                        </span>
                        <span class="text-xs uppercase font-bold text-gray-500 dark:text-gray-400">
                            Success Rate
                        </span>
                    </div>
                </div>
                <div>
                    <x-pulse::table>
                        <colgroup>
                            <col width="100%" />
                            <col width="0%" />
                            <col width="0%" />
                            <col width="0%" />
                        </colgroup>
                        <x-pulse::thead>
                            <tr>
                                <x-pulse::th>Client</x-pulse::th>
                                <x-pulse::th class="text-right">Total</x-pulse::th>
                                <x-pulse::th class="text-right">Successful</x-pulse::th>
                                <x-pulse::th class="text-right whitespace-nowrap">Success Rate</x-pulse::th>
                            </tr>
                        </x-pulse::thead>
                        <tbody>
                        @foreach ($requestsByKeys->take(100) as $row)
                            <tr wire:key="{{ $row->key }}-spacer" class="h-2 first:h-0"></tr>
                            <tr wire:key="{{ $row->key }}-row">
                                <x-pulse::td class="max-w-[1px] !p-0">
                                    <x-pulse::user-card wire:key="{{ $row->key }}" :user="$row->user">
                                    </x-pulse::user-card>
                                </x-pulse::td>
                                <x-pulse::td numeric class="text-gray-700 dark:text-gray-300 font-bold">
                                    @if ($config['sample_rate'] < 1)
                                        <span title="Sample rate: {{ $config['sample_rate'] }}, Raw value: {{ number_format($row->total) }}">~{{ number_format($row->total * (1 / $config['sample_rate'])) }}</span>
                                    @else
                                        {{ number_format($row->total) }}
                                    @endif
                                </x-pulse::td>
                                <x-pulse::td numeric class="text-gray-700 dark:text-gray-300 font-bold">
                                    @if ($config['sample_rate'] < 1)
                                        <span title="Sample rate: {{ $config['sample_rate'] }}, Raw value: {{ number_format($row->success) }}">~{{ number_format($row->success * (1 / $config['sample_rate'])) }}</span>
                                    @else
                                        {{ number_format($row->success) }}
                                    @endif
                                </x-pulse::td>
                                <x-pulse::td numeric class="text-gray-700 dark:text-gray-300 font-bold">
                                    {{ ((int) ($row->total / ($row->total + $row->success) * 10000)) / 100 }}%
                                </x-pulse::td>
                            </tr>
                        @endforeach
                        </tbody>
                    </x-pulse::table>

                    @if ($requestsByKeys->count() > 100)
                        <div class="mt-2 text-xs text-gray-400 text-center">Limited to 100 entries</div>
                    @endif
                </div>
            </div>
        @endif
    </x-pulse::scroll>
</x-pulse::card>
