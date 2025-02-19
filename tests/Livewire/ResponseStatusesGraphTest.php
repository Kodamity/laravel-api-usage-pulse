<?php

declare(strict_types=1);

/**
 * Created by PhpStorm.
 * Author: Misha Serenkov
 * Email: mi.serenkov@gmail.com
 * Date: 19.02.2025 13:24
 */

namespace Kodamity\Libraries\ApiUsagePulse\Tests\Livewire;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Kodamity\Libraries\ApiUsagePulse\Enums\ResponseStatusGroup;
use Kodamity\Libraries\ApiUsagePulse\Livewire\ResponseStatusesGraph;
use Kodamity\Libraries\ApiUsagePulse\Recorders\ResponsesStatistics;
use Kodamity\Libraries\ApiUsagePulse\Tests\Recorders\ResponsesStatisticsTest;
use Kodamity\Libraries\ApiUsagePulse\Tests\TestCase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Test;
use Workbench\App\Models\User;

class ResponseStatusesGraphTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Test',
            'email' => 'test@test.com',
            'password' => Hash::make('test'),
        ]);

        Gate::define('viewPulse', fn ($user = null) => true);
    }

    #[Test]
    public function it_exists_on_pulse_dashboard(): void
    {
        /* @phpstan-ignore method.notFound (Method 'assertSeeLivewire' exists but not resolved due to laravel magic) */
        $this
            ->actingAs($this->user)
            ->get('/pulse')
            ->assertSeeLivewire(ResponseStatusesGraph::class);
    }

    #[Test]
    public function it_displays_no_results(): void
    {
        $this->get('/test/informational')->assertStatus(100);
        $this->get('/test/client-error')->assertStatus(400);
        $this->get('/test/server-error')->assertStatus(500);

        Livewire::actingAs($this->user)
            ->withoutLazyLoading()
            ->test(ResponseStatusesGraph::class)
            ->assertSeeText('Response Statuses')
            ->assertSeeText('No results')
            ->assertViewHas('datasets', static fn (Collection $datasets) => $datasets->isEmpty());
    }

    #[Test, DataProviderExternal(ResponsesStatisticsTest::class, 'responseStatusGroupsProvider')]
    public function it_displays_graph_of_one_of_response_statuses_group(ResponseStatusGroup $statusGroup, int $expectedStatusCode): void
    {
        Date::setTestNow('2025-02-18 03:04:05');

        $testUrl = Str::of($statusGroup->name)->kebab()->prepend('/test/')->toString();

        Config::set('pulse.recorders.' . ResponsesStatistics::class . '.records', [$statusGroup->value]);

        $this->get($testUrl)->assertStatus($expectedStatusCode);

        Livewire::actingAs($this->user)
            ->withoutLazyLoading()
            ->test(ResponseStatusesGraph::class)
            ->assertSeeText('Response Statuses')
            ->assertSeeText(sprintf('%dxx', ((string)$expectedStatusCode)[0]))
            ->assertViewHas('datasets', function (Collection $datasets) use ($statusGroup): bool {
                $conditions = $datasets
                    ->map(function (Collection $dataset, $key) use ($statusGroup): bool {
                        if ($key === $statusGroup->name) {
                            return $dataset->filter()->containsOneItem();
                        }

                        return $dataset->filter()->isEmpty();
                    })
                    ->values()
                    ->all();

                return !in_array(false, $conditions, true);
            });
    }

    #[Test]
    public function it_displays_graph_of_all_of_response_statuses_group(): void
    {
        Date::setTestNow('2025-02-18 03:04:05');

        Config::set('pulse.recorders.' . ResponsesStatistics::class . '.records', array_map(
            fn (ResponseStatusGroup $statusGroup) => $statusGroup->value,
            ResponseStatusGroup::cases(),
        ));

        $this->get('/test/informational')->assertStatus(100);
        $this->get('/test/successful')->assertStatus(200);
        $this->get('/test/redirection')->assertStatus(302);
        $this->get('/test/client-error')->assertStatus(400);
        $this->get('/test/server-error')->assertStatus(500);

        Livewire::actingAs($this->user)
            ->withoutLazyLoading()
            ->test(ResponseStatusesGraph::class)
            ->assertSeeText('Response Statuses')
            ->assertSeeTextInOrder(['1xx', '2xx', '3xx', '4xx', '5xx'])
            ->assertViewHas('datasets', function (Collection $datasets): bool {
                $conditions = $datasets
                    ->map(fn (Collection $dataset): bool => $dataset->filter()->containsOneItem())
                    ->values()
                    ->all();

                return !in_array(false, $conditions, true);
            });
    }

    #[Test]
    public function it_dispatches_updated_response_statuses_graph_event(): void
    {
        Date::setTestNow('2025-02-18 03:04:05');

        Config::set('pulse.recorders.' . ResponsesStatistics::class . '.ignore', [
            '#^/livewire#',
        ]);
        Config::set('pulse.recorders.' . ResponsesStatistics::class . '.records', array_map(
            fn (ResponseStatusGroup $statusGroup) => $statusGroup->value,
            ResponseStatusGroup::cases(),
        ));

        $component = Livewire::actingAs($this->user)
            ->withoutLazyLoading()
            ->test(ResponseStatusesGraph::class)
            ->assertSeeText('Response Statuses')
            ->assertSeeText('No results');

        Date::setTestNow('2025-02-18 03:05:05');

        $this->get('/test/informational')->assertStatus(100);
        $this->get('/test/successful')->assertStatus(200);
        $this->get('/test/redirection')->assertStatus(302);
        $this->get('/test/client-error')->assertStatus(400);
        $this->get('/test/server-error')->assertStatus(500);

        $component->update()
            ->assertDispatched('kdm-api-usage-response-statuses-chart-update', function (string $eventName, array $params) {
                $conditions = collect($params['datasets'] ?? [])
                    ->map(fn (array $dataset): bool => collect($dataset)->filter()->containsOneItem())
                    ->values()
                    ->all();

                return !in_array(false, $conditions, true);
            });
    }
}
