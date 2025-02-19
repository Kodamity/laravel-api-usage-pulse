<?php

declare(strict_types=1);

/**
 * Created by PhpStorm.
 * Author: Misha Serenkov
 * Email: mi.serenkov@gmail.com
 * Date: 16.02.2025 15:29
 */

namespace Kodamity\Libraries\ApiUsagePulse\Livewire;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use Kodamity\Libraries\ApiUsagePulse\Enums\RecordKeys;
use Kodamity\Libraries\ApiUsagePulse\Enums\ResponseStatusGroup;
use Kodamity\Libraries\ApiUsagePulse\Recorders\ResponsesStatistics;
use Laravel\Pulse\Livewire\Card;
use Laravel\Pulse\Livewire\Concerns;
use Livewire\Attributes\Lazy;
use Livewire\Livewire;

/**
 * @internal
 */
#[Lazy]
class ResponseStatusesGraph extends Card
{
    use Concerns\HasPeriod;
    use Concerns\RemembersQueries;

    public function render(): Renderable
    {
        $statuses = array_map(static fn (ResponseStatusGroup $statusGroup) => $statusGroup->value, ResponseStatusGroup::cases());

        /* @phpstan-ignore return.type */
        [$requests, $time, $runAt] = $this->remember(fn () => $this->graph($statuses, 'count'));

        $datasets = $requests
            ->get(RecordKeys::ResponsesStatistics->value, collect())
            ->mapWithKeys(function (Collection $item, string $key) {
                return [ResponseStatusGroup::from($key)->name => $item];
            });

        if (Livewire::isLivewireRequest()) {
            $this->dispatch('kdm-api-usage-response-statuses-chart-update', datasets: $datasets);
        }

        $recordStatusFlag = [];
        foreach (ResponseStatusGroup::cases() as $status) {
            $recordStatusFlag['record_' . $status->name] = in_array(
                $status->value,
                Config::get('pulse.recorders.' . ResponsesStatistics::class . '.records', []),
                true,
            );
        }

        return View::make('api-usage-pulse::livewire.response-statuses-graph', [
            'datasets' => $datasets,
            'time' => $time,
            'runAt' => $runAt,
            'config' => [
                'sample_rate' => Config::get('pulse.recorders.' . ResponsesStatistics::class . '.sample_rate'),
                ...$recordStatusFlag,
            ],
        ]);
    }
}
