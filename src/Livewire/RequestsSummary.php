<?php

declare(strict_types=1);

/**
 * Created by PhpStorm.
 * Author: Misha Serenkov
 * Email: mi.serenkov@gmail.com
 * Date: 16.02.2025 18:22
 */

namespace Kodamity\Libraries\ApiUsagePulse\Livewire;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use Kodamity\Libraries\ApiUsagePulse\Enums\PulseRecordTypes;
use Kodamity\Libraries\ApiUsagePulse\Recorders\RequestsStatistics;
use Laravel\Pulse\Facades\Pulse;
use Laravel\Pulse\Livewire\Card;
use Laravel\Pulse\Livewire\Concerns;
use Livewire\Attributes\Lazy;

/**
 * @internal
 */
#[Lazy]
class RequestsSummary extends Card
{
    use Concerns\HasPeriod;
    use Concerns\RemembersQueries;

    /**
     * Render the component.
     */
    public function render(): Renderable
    {
        $types = [PulseRecordTypes::RequestsStatisticsTotal->value, PulseRecordTypes::RequestsStatisticsSuccessful->value];

        [$totalRequests, $allTime, $allRunAt] = $this->remember(
            fn () => with(
                $this->aggregateTotal($types, 'count'),
                fn ($results) => (object) [
                    'total' => $results[PulseRecordTypes::RequestsStatisticsTotal->value] ?? 0,
                    'success' => $results[PulseRecordTypes::RequestsStatisticsSuccessful->value] ?? 0,
                ],
            ),
            'all',
        );

        [$requestsByKeys, $keyTime, $keyRunAt] = $this->remember(
            function () use ($types) {
                $requestsByKeys = $this->aggregateTypes($types, 'count')
                    ->map(function ($row) {
                        return (object) [
                            'key' => $row->key,
                            'total' => $row->{PulseRecordTypes::RequestsStatisticsTotal->value} ?? 0,
                            'success' => $row->{PulseRecordTypes::RequestsStatisticsSuccessful->value} ?? 0,
                        ];
                    });

                $users = Pulse::resolveUsers($requestsByKeys->pluck('key'));

                return $requestsByKeys->each(function ($row) use ($users) {
                    $row->user = $users->find($row->key);
                });
            },
            'keys',
        );

        return View::make('api-usage-pulse::livewire.requests-summary', [
            'allTime' => $allTime,
            'allRunAt' => $allRunAt,
            'totalRequests' => $totalRequests,
            'keyTime' => $keyTime,
            'keyRunAt' => $keyRunAt,
            'requestsByKeys' => $requestsByKeys,
            'config' => Config::get('pulse.recorders.' . RequestsStatistics::class),
        ]);
    }
}
