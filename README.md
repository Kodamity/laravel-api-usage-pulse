<p align="center"><img src="https://raw.githubusercontent.com/Kodamity/.github/refs/heads/main/art/logo.svg" alt="Kodamity Logo"></p>

# API usage cards for Laravel Pulse

<p align="center">
<a href="https://github.com/kodamity/laravel-api-usage-pulse/actions?query=workflow%3Atests+branch%3Amain"><img src="https://img.shields.io/github/actions/workflow/status/kodamity/laravel-api-usage-pulse/tests.yml?branch=main&label=tests&style=flat-square" alt="Build Status"></a>
<a href="https://packagist.org/packages/kodamity/laravel-api-usage-pulse"><img src="https://img.shields.io/packagist/dt/kodamity/laravel-api-usage-pulse.svg?style=flat-square" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/kodamity/laravel-api-usage-pulse"><img src="https://img.shields.io/packagist/v/kodamity/laravel-api-usage-pulse.svg?style=flat-square" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/kodamity/laravel-api-usage-pulse"><img src="https://img.shields.io/packagist/l/kodamity/laravel-api-usage-pulse" alt="License"></a>
</p>


This is package that adds API usage cards and recorders for Laravel Pulse.

## Installation

You can install the package via composer:

```bash
composer require kodamity/laravel-api-usage-pulse
```

## Recorders
Add recorders configuration inside `config/pulse.php`. (If you don\'t have this file make sure you have published the config file of Laravel Pulse using `php artisan vendor:publish --tag=pulse-config`)

### Requests Statistics recorder
Requests Statistics recorder records the number of requests and the number of successful requests in general for all users.
And it records the number of requests and the number of successful requests for each user.

```php
return [
    // ...
    
    'recorders' => [
        // Existing recorders...
        
        \Kodamity\Libraries\ApiUsagePulse\Recorders\RequestsStatistics::class => [
            'enabled' => env('PULSE_KDM_API_USAGE_REQUESTS_STATISTICS_ENABLED', true),
            'sample_rate' => env('PULSE_KDM_API_USAGE_REQUESTS_STATISTICS_SAMPLE_RATE', 1),
            'ignore' => [
                '#^/pulse$#', // Pulse dashboard...
            ],
        ],
    ],
];
```

## Dashboards

To add the card to the Pulse dashboard, you must first [publish the vendor view](https://laravel.com/docs/11.x/pulse#dashboard-customization).

```bash
php artisan vendor:publish --tag=pulse-dashboard
```

Then, you can modify the `dashboard.blade.php` file and add the card to the dashboard.

```php
<livewire:kodamity.pulse.api-usage.requests-summary cols="6" />
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Misha Serenkov](https://github.com/miserenkov)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
