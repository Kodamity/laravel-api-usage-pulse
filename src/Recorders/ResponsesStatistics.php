<?php

declare(strict_types=1);

/**
 * Created by PhpStorm.
 * Author: Misha Serenkov
 * Email: mi.serenkov@gmail.com
 * Date: 16.02.2025 15:04
 */

namespace Kodamity\Libraries\ApiUsagePulse\Recorders;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Kodamity\Libraries\ApiUsagePulse\Enums\RecordKeys;
use Kodamity\Libraries\ApiUsagePulse\Enums\RecordTypes;
use Kodamity\Libraries\ApiUsagePulse\Enums\ResponseStatusGroup;
use Laravel\Pulse\Concerns\ConfiguresAfterResolving;
use Laravel\Pulse\Pulse;
use Laravel\Pulse\Recorders\Concerns;
use Symfony\Component\HttpFoundation\Response;

class ResponsesStatistics
{
    use Concerns\Ignores;
    use Concerns\LivewireRoutes;
    use Concerns\Sampling;
    use Concerns\Thresholds;
    use ConfiguresAfterResolving;

    /**
     * Create a new recorder instance.
     */
    public function __construct(
        protected Pulse $pulse,
    ) {
        //
    }

    protected function shouldRecord(ResponseStatusGroup $statusGroup): bool
    {
        return in_array($statusGroup->value, Config::get('pulse.recorders.' . static::class . '.records', []));
    }

    /**
     * Register the recorder.
     */
    public function register(callable $record, Application $app): void
    {
        $this->afterResolving(
            $app,
            Kernel::class,
            fn (Kernel $kernel) => $kernel->whenRequestLifecycleIsLongerThan(-1, $record),
        );
    }

    /**
     * Record the request.
     */
    public function record(Carbon $startedAt, Request $request, Response $response): void
    {
        $this->pulse->lazy(function () use ($startedAt, $request, $response) {
            if (!$this->shouldSample() || $this->shouldIgnore(Str::start($request->path(), '/'))) {
                return;
            }

            $statusCode = $response->getStatusCode();
            $statusGroup = ResponseStatusGroup::fromStatusCode($statusCode);

            if (!$this->shouldRecord($statusGroup)) {
                return;
            }

            $this->pulse->record(
                type: $statusGroup->value,
                key: RecordKeys::ResponsesStatistics->value,
                timestamp: $startedAt,
            )->count()->onlyBuckets();

            $duration = ((int) $startedAt->diffInMilliseconds());
            $this->pulse->record(
                type: RecordTypes::ResponsesStatisticsTime->value,
                key: RecordKeys::ResponsesStatistics->value,
                value: $duration,
                timestamp: $startedAt,
            )->avg()->onlyBuckets();
        });
    }
}
