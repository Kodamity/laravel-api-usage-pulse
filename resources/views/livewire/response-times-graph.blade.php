@php
    /** @var \Illuminate\Support\Collection $dataset */
@endphp

<x-pulse::card :cols="$cols" :rows="$rows" :class="$class">
    <x-pulse::card-header
        name="Response Times"
        x-bind:title="Time: {{ number_format($time) }}ms; Run at: {{ $runAt }};"
        details="past {{ $this->periodForHumans() }}">
        <x-slot:icon>
            <x-pulse::icons.clock />
        </x-slot:icon>
        @if (!$dataset->isEmpty())
            <x-slot:actions>
                <div class="flex flex-wrap gap-4">
                    <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400 font-medium">
                        <div class="h-0.5 w-3 rounded-full" style="background-color: rgba(29,153,172,0.5)"></div>
                        Average Response Time (ms)
                    </div>
                </div>
            </x-slot:actions>
        @endif
    </x-pulse::card-header>

    <x-pulse::scroll :expand="$expand" wire:poll.5s="">
        @if ($dataset->isEmpty())
            <x-pulse::no-results />
        @else
            <div class="grid gap-3 mx-px mb-px">
                <div wire:key="response-times">
                    @php
                        $highest = $dataset->max();
                    @endphp

                    <div class="mt-3 relative">
                        <div
                            class="absolute -left-px -top-2 max-w-fit h-4 flex items-center px-1 text-xs leading-none text-white font-bold bg-purple-500 rounded after:[--triangle-size:4px] after:border-l-purple-500 after:absolute after:right-[calc(-1*var(--triangle-size))] after:top-[calc(50%-var(--triangle-size))] after:border-t-[length:var(--triangle-size)] after:border-b-[length:var(--triangle-size)] after:border-l-[length:var(--triangle-size)] after:border-transparent">
                            @if ($config['sample_rate'] < 1) <span
                                title="Sample rate: {{ $config['sample_rate'] }}, Raw value: {{ number_format($highest) }}">
                            ~{{ number_format($highest * (1 / $config['sample_rate'])) }}</span>
                            @else
                                {{ number_format($highest) }} ms.
                            @endif
                        </div>

                        <div wire:ignore class="" x-data="kdmResponseTimesChart({
                                    dataset: @js($dataset),
                                    sampleRate: {{ $config['sample_rate'] }}
                                })">
                            <canvas x-ref="canvas"
                                    class="h-52 ring-1 ring-gray-900/5 dark:ring-gray-100/10 bg-gray-50 dark:bg-gray-800 rounded-md shadow-sm"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </x-pulse::scroll>
</x-pulse::card>

@script
<script>
    Alpine.data('kdmResponseTimesChart', (data) => ({
        init() {
            var dataset = data.dataset;

            let chart = new Chart(
                this.$refs.canvas,
                {
                    type: 'line',
                    data: {
                        labels: Object.keys(dataset),
                        datasets: [
                            {
                                label: 'Average Response Time',
                                borderColor: 'rgba(29,153,172,0.5)',
                                borderWidth: 2,
                                borderCapStyle: 'round',
                                data: this.scale(dataset),
                                pointHitRadius: 10,
                                pointStyle: false,
                                tension: 0.2,
                                spanGaps: false,
                            },
                        ],
                    },
                    options: {
                        maintainAspectRatio: false,
                        layout: {
                            autoPadding: false,
                            padding: {
                                top: 3,
                            },
                        },
                        dataset: {
                            line: {
                                borderWidth: 2,
                                borderCapStyle: 'round',
                                pointHitRadius: 10,
                                pointStyle: false,
                                tension: 0.2,
                                spanGaps: false,
                                segment: {
                                    borderColor: (ctx) => ctx.p0.raw === 0 && ctx.p1.raw === 0 ? 'transparent' : undefined,
                                }
                            }
                        },
                        scales: {
                            x: {
                                display: false,
                            },
                            y: {
                                display: false,
                                min: 0,
                                max: this.highest(dataset),
                            },
                        },
                        plugins: {
                            legend: {
                                display: false,
                            },
                            tooltip: {
                                mode: 'index',
                                position: 'nearest',
                                intersect: false,
                                callbacks: {
                                    beforeBody: (context) => context
                                        .filter(item => item.raw > 0)
                                        .map(item => `${item.dataset.label}: ${data.sampleRate < 1 ? '~' : ''}${item.formattedValue} ms`)
                                        .join(', '),
                                    label: () => null,
                                },
                            },
                        },
                    },
                }
            )

            Livewire.on('kdm-api-usage-response-times-chart-update', ({ dataset }) => {
                if (chart === undefined) {
                    return;
                }

                if (dataset === undefined && chart) {
                    chart.destroy();
                    chart = undefined;
                    return;
                }

                chart.data.labels = Object.keys(dataset);
                chart.options.scales.y.max = this.highest(dataset);
                chart.data.datasets[0].data = this.scale(dataset);
                chart.update();
            });
        },

        scale(status) {
            return Object.values(status).map(value => value * (1 / data.sampleRate ));
        },

        highest(dataset) {
            return Math.max(...Object.values(dataset));
        }
    }))
</script>
@endscript
