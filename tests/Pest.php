<?php

use Illuminate\Support\Facades\Gate;
use Kodamity\Libraries\ApiUsagePulse\Tests\TestCase;

uses(TestCase::class)
    ->beforeEach(function () {
        Gate::define('viewPulse', fn ($user = null) => true);
    })
    ->in(__DIR__);
