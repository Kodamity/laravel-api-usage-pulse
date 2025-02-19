<?php

declare(strict_types=1);

/**
 * Created by PhpStorm.
 * Author: Misha Serenkov
 * Email: mi.serenkov@gmail.com
 * Date: 19.02.2025 10:56
 */

namespace Kodamity\Libraries\ApiUsagePulse\Tests\Livewire;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Kodamity\Libraries\ApiUsagePulse\Livewire\RequestsSummary;
use Kodamity\Libraries\ApiUsagePulse\Tests\TestCase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use stdClass;
use Workbench\App\Models\User;

class RequestsSummaryTest extends TestCase
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
            ->assertSeeLivewire(RequestsSummary::class);
    }

    #[Test]
    public function it_displays_summary_of_requests(): void
    {
        $this->actingAs($this->user);

        $this->get('/test/informational')->assertStatus(100);
        $this->get('/test/successful')->assertStatus(200);
        $this->get('/test/redirection')->assertStatus(302);
        $this->get('/test/client-error')->assertStatus(400);
        $this->get('/test/server-error')->assertStatus(500);

        Livewire::actingAs($this->user)
            ->withoutLazyLoading()
            ->test(RequestsSummary::class)
            ->assertSeeText('Requests Summary')
            ->assertViewHas('totalRequests', function (stdClass $totalRequests) {
                return $totalRequests->total === 5 && $totalRequests->success === 1;
            });
    }

    #[Test]
    public function it_displays_no_results(): void
    {
        $this->get('/test/informational')->assertStatus(100);
        $this->get('/test/client-error')->assertStatus(400);
        $this->get('/test/server-error')->assertStatus(500);

        Livewire::actingAs($this->user)
            ->withoutLazyLoading()
            ->test(RequestsSummary::class)
            ->assertSeeText('Requests Summary')
            ->assertSeeText('No results')
            ->assertViewHas('totalRequests', function (stdClass $totalRequests) {
                return $totalRequests->total === 0 && $totalRequests->success === 0;
            });
    }

    #[Test]
    public function it_displays_requests_summary_for_clients(): void
    {
        $this->actingAs($this->user);

        $this->get('/test/informational')->assertStatus(100);
        $this->get('/test/successful')->assertStatus(200);
        $this->get('/test/redirection')->assertStatus(302);
        $this->get('/test/client-error')->assertStatus(400);
        $this->get('/test/server-error')->assertStatus(500);

        Livewire::actingAs($this->user)
            ->withoutLazyLoading()
            ->test(RequestsSummary::class)
            ->assertSeeText('Requests Summary')
            ->assertViewHas('requestsByKeys', function (Collection $requestsByKeys): bool {
                return $requestsByKeys->containsOneItem() &&
                    $requestsByKeys->first(function (stdClass $item): bool {
                        return $item->key === '1' && $item->total === 5 && $item->success === 1;
                    });
            });
    }
}
