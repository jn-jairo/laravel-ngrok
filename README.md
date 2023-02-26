[![Total Downloads](https://poser.pugx.org/jn-jairo/laravel-ngrok/downloads)](https://packagist.org/packages/jn-jairo/laravel-ngrok)
[![Latest Stable Version](https://poser.pugx.org/jn-jairo/laravel-ngrok/v/stable)](https://packagist.org/packages/jn-jairo/laravel-ngrok)
[![License](https://poser.pugx.org/jn-jairo/laravel-ngrok/license)](https://packagist.org/packages/jn-jairo/laravel-ngrok)

# Share Laravel application with ngrok

This package allows you to share your Laravel application with [ngrok](https://ngrok.com).

## Requirements

- Ngrok >= 2.2.8 (If you are using [Laravel Homestead](https://laravel.com/docs/homestead) this should be already installed)

## Version Compatibility

 Laravel  | Laravel Ngrok
:---------|:----------
  5.8.x   | 1.x
  6.x     | 1.x
  7.x     | 1.x
  8.x     | 2.x
  9.x     | 2.x
 10.x     | 3.x

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

## Advanced usage

```bash
php artisan ngrok [options] [--] [<host-header>]
```

 Argument        | Description
:----------------|:------------------------------------------------------
 **host-header** | Host header to identify the app (Example: myapp.test)

 Option                  | Description
:------------------------|:-----------------------------------------------------------
 **-H, --host[=HOST]**   | Host to tunnel the requests (default: localhost)
 **-P, --port[=PORT]**   | Port to tunnel the requests (default: 80)
 **-E, --extra[=EXTRA]** | Extra arguments to ngrok command (multiple values allowed)


## Examples

```bash
# If you have multiples apps (myapp.test, my-other-app.test, ...)
# set it in the app.url configuration
# or pass it in the host-header argument

php artisan ngrok myapp.test

# If you use a different port, set it in the app.url configuration
# or pass it in the --port option

php artisan ngrok --port=8000 myapp.test

# If you use docker and have containers like (nginx, php, workspace)
# and wanna run the command inside the workspace container
# pass the name of the container the requests will tunnel through

php artisan ngrok --host=nginx example.com

# If you wanna pass other arguments directly to ngrok
# use the --extra or -E option

php artisan ngrok --extra='--region=eu' -E'--config=ngrok.yml'

```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
