<?php

namespace Apility\Laravel\Ngrok\Tests;

use Apility\Laravel\Ngrok\NgrokServiceProvider;
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
