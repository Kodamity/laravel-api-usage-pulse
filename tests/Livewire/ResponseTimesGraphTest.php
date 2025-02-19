<?php

declare(strict_types=1);

/**
 * Created by PhpStorm.
 * Author: Misha Serenkov
 * Email: mi.serenkov@gmail.com
 * Date: 19.02.2025 16:00
 */

namespace Kodamity\Libraries\ApiUsagePulse\Tests\Livewire;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Kodamity\Libraries\ApiUsagePulse\Enums\ResponseStatusGroup;
use Kodamity\Libraries\ApiUsagePulse\Livewire\ResponseTimesGraph;
use Kodamity\Libraries\ApiUsagePulse\Recorders\ResponsesStatistics;
use Kodamity\Libraries\ApiUsagePulse\Tests\TestCase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Workbench\App\Models\User;

class ResponseTimesGraphTest extends TestCase
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
            ->assertSeeLivewire(ResponseTimesGraph::class);
    }

    #[Test]
    public function it_displays_no_results(): void
    {
        $this->get('/test/informational')->assertStatus(100);
        $this->get('/test/client-error')->assertStatus(400);
        $this->get('/test/server-error')->assertStatus(500);

        Livewire::actingAs($this->user)
            ->withoutLazyLoading()
            ->test(ResponseTimesGraph::class)
            ->assertSeeText('Response Times')
            ->assertSeeText('No results')
            ->assertViewHas('dataset', static fn (Collection $dataset) => $dataset->isEmpty());
    }

    #[Test]
    public function it_displays_graph_of_response_times(): void
    {
        Config::set('pulse.recorders.' . ResponsesStatistics::class . '.records', array_map(
            fn (ResponseStatusGroup $statusGroup) => $statusGroup->value,
            ResponseStatusGroup::cases(),
        ));

        $this->get('/test/long-response')->assertStatus(200);

        $this->get('/test/long-response')->assertStatus(200);

        Livewire::actingAs($this->user)
            ->withoutLazyLoading()
            ->test(ResponseTimesGraph::class)
            ->assertSeeText('Response Times')
            ->assertViewHas('dataset', function (Collection $dataset): bool {
                return $dataset->filter(static fn (int|null $avgTime): bool => $avgTime > 0)->isNotEmpty();
            });
    }

    #[Test]
    public function it_dispatches_updated_response_statuses_graph_event(): void
    {
        Config::set('pulse.recorders.' . ResponsesStatistics::class . '.ignore', [
            '#^/livewire#',
        ]);
        Config::set('pulse.recorders.' . ResponsesStatistics::class . '.records', array_map(
            fn (ResponseStatusGroup $statusGroup) => $statusGroup->value,
            ResponseStatusGroup::cases(),
        ));

        $component = Livewire::actingAs($this->user)
            ->withoutLazyLoading()
            ->test(ResponseTimesGraph::class)
            ->assertSeeText('Response Times')
            ->assertSeeText('No results');

        $this->get('/test/long-response')->assertStatus(200);

        Date::setTestNow(now()->addMinute());

        $component->update()
            ->assertDispatched('kdm-api-usage-response-times-chart-update', function (string $eventName, array $params) {
                return collect($params['dataset'] ?? [])
                    ->filter(static fn (int|null $avgTime): bool => $avgTime > 0)
                    ->isNotEmpty();
            });
    }
}
