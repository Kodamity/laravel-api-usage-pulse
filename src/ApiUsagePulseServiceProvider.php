<?php

namespace Kodamity\Libraries\ApiUsagePulse;

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
}
