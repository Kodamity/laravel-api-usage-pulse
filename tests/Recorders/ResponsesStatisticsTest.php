<?php

declare(strict_types=1);

/**
 * Created by PhpStorm.
 * Author: Misha Serenkov
 * Email: mi.serenkov@gmail.com
 * Date: 18.02.2025 21:38
 */

namespace Kodamity\Libraries\ApiUsagePulse\Tests\Recorders;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Kodamity\Libraries\ApiUsagePulse\Enums\PulseRecordTypes;
use Kodamity\Libraries\ApiUsagePulse\Enums\ResponseStatusGroup;
use Kodamity\Libraries\ApiUsagePulse\Recorders\ResponsesStatistics;
use Kodamity\Libraries\ApiUsagePulse\Tests\TestCase;
use Laravel\Pulse\Facades\Pulse;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class ResponsesStatisticsTest extends TestCase
{
    #[Test, DataProvider('responseStatusGroupsProvider')]
    public function it_captures_responses_by_status_code_group(ResponseStatusGroup $statusGroup, int $expectedStatusCode): void
    {
        Date::setTestNow('2025-02-18 03:04:05');

        $testUrl = Str::of($statusGroup->name)->kebab()->prepend('/test/')->toString();

        Config::set('pulse.recorders.' . ResponsesStatistics::class . '.records', [$statusGroup->value]);

        $this->get($testUrl)->assertStatus($expectedStatusCode);

        Pulse::ignore(function () use ($statusGroup) {
            $aggregatesRows = DB::table('pulse_aggregates')
                ->where('type', $statusGroup->value)
                ->get();

            $this->assertCount(4, $aggregatesRows);

            $timesAggregatesRows = DB::table('pulse_aggregates')
                ->where('type', PulseRecordTypes::ResponsesStatisticsTime->value)
                ->get();

            $this->assertCount(4, $timesAggregatesRows);

            $this->assertDatabaseCount('pulse_entries', 0);
            $this->assertDatabaseCount('pulse_values', 0);
        });
    }

    #[Test, DataProvider('responseStatusGroupsProvider')]
    public function it_does_not_capture_responses_by_status_code_group(ResponseStatusGroup $statusGroup, int $expectedStatusCode): void
    {
        Date::setTestNow('2025-02-18 03:04:05');

        $testUrl = Str::of($statusGroup->name)->kebab()->prepend('/test/')->toString();

        $allGroupsToRecord = Config::get('pulse.recorders.' . ResponsesStatistics::class . '.records', []);
        Config::set(
            'pulse.recorders.' . ResponsesStatistics::class . '.records',
            array_filter($allGroupsToRecord, fn (string $group) => $group !== $statusGroup->value),
        );

        $this->get($testUrl)->assertStatus($expectedStatusCode);

        Pulse::ignore(function () {
            $this->assertDatabaseCount('pulse_aggregates', 0);
            $this->assertDatabaseCount('pulse_entries', 0);
            $this->assertDatabaseCount('pulse_values', 0);
        });
    }

    #[Test]
    public function it_does_not_capture_ignored_requests(): void
    {
        Date::setTestNow('2025-02-18 03:04:05');

        Config::set('pulse.recorders.' . ResponsesStatistics::class . '.records', [
            ResponseStatusGroup::Successful->value,
        ]);
        Config::set('pulse.recorders.' . ResponsesStatistics::class . '.ignore', ['#^/test/ignored#']);

        $this->get('/test/ignored/it-can-not-pulse')->assertStatus(200);

        Pulse::ignore(function () {
            $this->assertDatabaseCount('pulse_aggregates', 0);
            $this->assertDatabaseCount('pulse_entries', 0);
            $this->assertDatabaseCount('pulse_values', 0);
        });
    }

    public static function responseStatusGroupsProvider(): array
    {
        return [
            'informational' => [ResponseStatusGroup::Informational, 100],
            'successful' => [ResponseStatusGroup::Successful, 200],
            'redirection' => [ResponseStatusGroup::Redirection, 302],
            'client-error' => [ResponseStatusGroup::ClientError, 400],
            'server-error' => [ResponseStatusGroup::ServerError, 500],
        ];
    }
}
