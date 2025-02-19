<?php

declare(strict_types=1);

/**
 * Created by PhpStorm.
 * Author: Misha Serenkov
 * Email: mi.serenkov@gmail.com
 * Date: 18.02.2025 21:36
 */

namespace Kodamity\Libraries\ApiUsagePulse\Tests\Recorders;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Kodamity\Libraries\ApiUsagePulse\Enums\RecordTypes;
use Kodamity\Libraries\ApiUsagePulse\Recorders\RequestsStatistics;
use Kodamity\Libraries\ApiUsagePulse\Tests\TestCase;
use Laravel\Pulse\Facades\Pulse;
use PHPUnit\Framework\Attributes\Test;
use Workbench\App\Models\User;

class RequestsStatisticsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $user = User::create([
            'name' => 'Test',
            'email' => 'test@test.com',
            'password' => Hash::make('test'),
        ]);

        $this->actingAs($user);
    }

    #[Test]
    public function it_captures_requests_statistics(): void
    {
        $this->get('/test/informational')->assertStatus(100);
        $this->get('/test/redirection')->assertStatus(302);
        $this->get('/test/client-error')->assertStatus(400);
        $this->get('/test/server-error')->assertStatus(500);

        Pulse::ignore(function () {
            $aggregatesRows = DB::table('pulse_aggregates')
                ->where('type', RecordTypes::RequestsStatisticsTotal)
                ->where('aggregate', 'count')
                ->get();
            $this->assertCount(4, $aggregatesRows);

            foreach ($aggregatesRows as $aggregateRow) {
                $this->assertSame(4, $aggregateRow->value);
            }

            $entriesRows = DB::table('pulse_entries')
                ->where('type', RecordTypes::RequestsStatisticsTotal)
                ->get();

            $this->assertCount(4, $entriesRows);

            $this->assertDatabaseCount('pulse_values', 0);
        });
    }

    #[Test]
    public function it_captures_successful_requests_statistics(): void
    {
        $this->get('/test/successful')->assertStatus(200);
        $this->get('/test/successful')->assertStatus(200);
        $this->get('/test/redirection')->assertStatus(302);
        $this->get('/test/client-error')->assertStatus(400);

        Pulse::ignore(function () {
            $aggregatesRows = DB::table('pulse_aggregates')
                ->where('type', RecordTypes::RequestsStatisticsTotal)
                ->where('aggregate', 'count')
                ->get();
            $this->assertCount(4, $aggregatesRows);

            $successfulAggregatesRows = DB::table('pulse_aggregates')
                ->where('type', RecordTypes::RequestsStatisticsSuccessful)
                ->where('aggregate', 'count')
                ->get();
            $this->assertCount(4, $successfulAggregatesRows);

            foreach ($successfulAggregatesRows as $aggregateRow) {
                $this->assertSame(2, $aggregateRow->value);
            }

            $entriesRows = DB::table('pulse_entries')
                ->where('type', RecordTypes::RequestsStatisticsSuccessful)
                ->get();

            $this->assertCount(2, $entriesRows);
        });
    }

    #[Test]
    public function it_does_not_capture_ignored_requests(): void
    {
        Config::set('pulse.recorders.' . RequestsStatistics::class . '.ignore', ['#^/test/ignored#']);

        $this->get('/test/ignored/it-can-not-pulse')->assertStatus(200);

        Pulse::ignore(function () {
            $this->assertDatabaseCount('pulse_aggregates', 0);
            $this->assertDatabaseCount('pulse_entries', 0);
            $this->assertDatabaseCount('pulse_values', 0);
        });
    }
}
