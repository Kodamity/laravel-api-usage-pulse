<?php

declare(strict_types=1);

/**
 * Created by PhpStorm.
 * Author: Misha Serenkov
 * Email: mi.serenkov@gmail.com
 * Date: 16.02.2025 18:46
 */

namespace Kodamity\Libraries\ApiUsagePulse\Recorders;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Kodamity\Libraries\ApiUsagePulse\Enums\PulseRecordTypes;
use Laravel\Pulse\Concerns\ConfiguresAfterResolving;
use Laravel\Pulse\Pulse;
use Laravel\Pulse\Recorders\Concerns;
use Symfony\Component\HttpFoundation\Response;

class RequestsStatistics
{
    use Concerns\Ignores;
    use Concerns\Sampling;
    use Concerns\LivewireRoutes;
    use ConfiguresAfterResolving;

    /**
     * Create a new recorder instance.
     */
    public function __construct(
        protected Pulse      $pulse,
        protected Repository $config,
    )
    {
        //
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
            if (($userId = $this->pulse->resolveAuthenticatedUserId()) === null || !$this->shouldSample()) {
                return;
            }

            $path = $request->path();
            if ($request->route()) {
                $path = $this->resolveRoutePath($request)[0];
            }

            if ($this->shouldIgnore($path)) {
                return;
            }

            $this->pulse->record(
                type: PulseRecordTypes::RequestsStatisticsTotal->value,
                key: (string)$userId,
                timestamp: $startedAt->getTimestamp(),
            )->count();

            if ($response->isSuccessful()) {
                $this->pulse->record(
                    type: PulseRecordTypes::RequestsStatisticsSuccessful->value,
                    key: (string)$userId,
                    timestamp: $startedAt->getTimestamp(),
                )->count();
            }
        });
    }
}
