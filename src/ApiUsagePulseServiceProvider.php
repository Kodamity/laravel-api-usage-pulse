<?php

namespace Kodamity\Libraries\ApiUsagePulse;

use Livewire\LivewireManager;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ApiUsagePulseServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-api-usage-pulse')
            ->hasConfigFile()
            ->hasViews();
    }

    public function packageBooted(): void
    {
        $this->callAfterResolving('livewire', function (LivewireManager $livewire) {
            $livewire->component('kodamity.pulse.api-usage.requests-summary', Livewire\RequestsSummary::class);
            $livewire->component('kodamity.pulse.api-usage.response-statuses-graph', Livewire\ResponseStatusesGraph::class);
            $livewire->component('kodamity.pulse.api-usage.response-times-graph', Livewire\ResponseTimesGraph::class);
        });
    }
}
