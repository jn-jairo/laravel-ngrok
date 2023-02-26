<?php

namespace JnJairo\Laravel\Ngrok\Tests;

use JnJairo\Laravel\Ngrok\NgrokServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            NgrokServiceProvider::class,
        ];
    }
}
