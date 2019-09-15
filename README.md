[![Build Status](https://travis-ci.com/jn-jairo/laravel-ngrok.svg?branch=master)](https://travis-ci.com/jn-jairo/laravel-ngrok)
[![Total Downloads](https://poser.pugx.org/jn-jairo/laravel-ngrok/downloads)](https://packagist.org/packages/jn-jairo/laravel-ngrok)
[![Latest Stable Version](https://poser.pugx.org/jn-jairo/laravel-ngrok/v/stable)](https://packagist.org/packages/jn-jairo/laravel-ngrok)
[![License](https://poser.pugx.org/jn-jairo/laravel-ngrok/license)](https://packagist.org/packages/jn-jairo/laravel-ngrok)

# Share Laravel application with ngrok

This package allows you to share your Laravel application with [ngrok](https://ngrok.com).

## Requirements

- Laravel Framework >= 5.8
- Ngrok >= 2.2.8 (If you are using [Laravel Homestead](https://laravel.com/docs/homestead) this should be already installed)

## Installation

You can install the package via composer:

```bash
composer require --dev jn-jairo/laravel-ngrok
```

The `NgrokServiceProvider` will be automatically registered for you.

## Usage

Just call the artisan command to start the ngrok.

```bash
php artisan ngrok
```

The parameters for ngrok will be extracted from your application.

You can also pass custom host and port.

```bash
php artisan ngrok example.com --port=8000
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
