<?php

namespace JnJairo\Laravel\Ngrok\Tests;

use JnJairo\Laravel\Ngrok\NgrokServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class OrchestraTestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            NgrokServiceProvider::class,
        ];
    }
}
