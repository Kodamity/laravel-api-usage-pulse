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

## Register the recorders
Add the `SomeRecords` inside `config/pulse.php`. (If you don\'t have this file make sure you have published the config file of Laravel Pulse using `php artisan vendor:publish --tag=pulse-config`)


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
