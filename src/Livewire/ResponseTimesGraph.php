<?php

declare(strict_types=1);

/**
 * Created by PhpStorm.
 * Author: Misha Serenkov
 * Email: mi.serenkov@gmail.com
 * Date: 17.02.2025 12:24
 */

namespace Kodamity\Libraries\ApiUsagePulse\Livewire;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use Kodamity\Libraries\ApiUsagePulse\Enums\PulseRecordKeys;
use Kodamity\Libraries\ApiUsagePulse\Enums\PulseRecordTypes;
use Kodamity\Libraries\ApiUsagePulse\Recorders\ResponsesStatistics;
use Laravel\Pulse\Livewire\Card;
use Laravel\Pulse\Livewire\Concerns;
use Livewire\Attributes\Lazy;
use Livewire\Livewire;

/**
 * @internal
 */
#[Lazy]
class ResponseTimesGraph extends Card
{
    use Concerns\HasPeriod;
    use Concerns\RemembersQueries;

    public function render(): Renderable
    {
        /* @phpstan-ignore return.type */
        [$requests, $time, $runAt] = $this->remember(fn () => $this->graph([PulseRecordTypes::ResponsesStatisticsTime->value], 'avg'));

        $dataset = $requests
            ->get(PulseRecordKeys::ResponsesStatistics->value, collect())
            ->get(PulseRecordTypes::ResponsesStatisticsTime->value, collect());

        if (Livewire::isLivewireRequest()) {
            $this->dispatch('kdm-api-usage-response-times-chart-update', dataset: $dataset);
        }

        return View::make('api-usage-pulse::livewire.response-times-graph', [
            'dataset' => $dataset,
            'time' => $time,
            'runAt' => $runAt,
            'config' => [
                'sample_rate' => Config::get('pulse.recorders.' . ResponsesStatistics::class . '.sample_rate'),
            ],
        ]);
    }
}
