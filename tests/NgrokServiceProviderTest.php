<?php

namespace JnJairo\Laravel\Ngrok\Tests;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\UrlGenerator;
use JnJairo\Laravel\Ngrok\NgrokProcessBuilder;
use JnJairo\Laravel\Ngrok\NgrokServiceProvider;
use JnJairo\Laravel\Ngrok\NgrokWebService;
use JnJairo\Laravel\Ngrok\Tests\OrchestraTestCase as TestCase;

/**
 * @testdox Ngrok service provider
 */
class NgrokServiceProviderTest extends TestCase
{
    public function test_boot_valid_ngrok_url() : void
    {
        $urlGenerator = $this->prophesize(UrlGenerator::class);
        $urlGenerator->forceScheme('http')->shouldBeCalled();
        $urlGenerator->forceRootUrl('http://00000000.ngrok.io')->shouldBeCalled();
        $urlGenerator->to(
            'foo',
            \Prophecy\Argument::any(),
            \Prophecy\Argument::any()
        )->willReturn('http://00000000.ngrok.io/foo')->shouldBeCalled();

        $request = Request::create(
            'http://example.com/foo',
            'GET',
            ['foo' => 'bar'],
            [],
            [],
            [
                'HTTP_X_ORIGINAL_HOST' => '00000000.ngrok.io',
            ]
        );

        $app = $this->prophesize(Application::class);
        $app->runningInConsole()->willReturn(false)->shouldBeCalled();
        $app->make('url')->willReturn($urlGenerator->reveal())->shouldBeCalled();
        $app->make('request')->willReturn($request)->shouldBeCalled();

        $serviceProvider = new NgrokServiceProvider($app->reveal());
        $serviceProvider->boot();

        $this->assertSame('http://00000000.ngrok.io/foo', Paginator::resolveCurrentPath());
    }

    public function test_boot_valid_secure_ngrok_url() : void
    {
        $urlGenerator = $this->prophesize(UrlGenerator::class);
        $urlGenerator->forceScheme('https')->shouldBeCalled();
        $urlGenerator->forceRootUrl('https://00000000.ngrok.io')->shouldBeCalled();
        $urlGenerator->to(
            'foo',
            \Prophecy\Argument::any(),
            \Prophecy\Argument::any()
        )->willReturn('https://00000000.ngrok.io/foo')->shouldBeCalled();

        $request = Request::create(
            'https://example.com/foo',
            'GET',
            ['foo' => 'bar'],
            [],
            [],
            [
                'HTTP_X_ORIGINAL_HOST' => '00000000.ngrok.io',
                'HTTP_X_FORWARDED_PROTO' => 'https',
            ]
        );

        $app = $this->prophesize(Application::class);
        $app->runningInConsole()->willReturn(false)->shouldBeCalled();
        $app->make('url')->willReturn($urlGenerator->reveal())->shouldBeCalled();
        $app->make('request')->willReturn($request)->shouldBeCalled();

        $serviceProvider = new NgrokServiceProvider($app->reveal());
        $serviceProvider->boot();

        $this->assertSame('https://00000000.ngrok.io/foo', Paginator::resolveCurrentPath());
    }

    public function test_boot_not_ngrok_url() : void
    {
        $urlGenerator = $this->prophesize(UrlGenerator::class);
        $urlGenerator->forceScheme(\Prophecy\Argument::any())->shouldNotBeCalled();
        $urlGenerator->forceRootUrl(\Prophecy\Argument::any())->shouldNotBeCalled();

        $request = Request::create(
            'http://example.com/foo',
            'GET',
            ['foo' => 'bar'],
            [],
            [],
            []
        );

        $app = $this->prophesize(Application::class);
        $app->runningInConsole()->willReturn(false)->shouldBeCalled();
        $app->make('url')->willReturn($urlGenerator->reveal())->shouldBeCalled();
        $app->make('request')->willReturn($request)->shouldBeCalled();

        $serviceProvider = new NgrokServiceProvider($app->reveal());
        $serviceProvider->boot();
    }

    public function test_boot_not_running_in_console() : void
    {
        $app = $this->prophesize(Application::class);
        $app->runningInConsole()->willReturn(true)->shouldBeCalled();
        $app->make('url')->shouldNotBeCalled();
        $app->make('request')->shouldNotBeCalled();

        $serviceProvider = new NgrokServiceProvider($app->reveal());
        $serviceProvider->boot();
    }

    public function test_register_bind() : void
    {
        $processBuilder = app(NgrokProcessBuilder::class);
        $this->assertInstanceOf(NgrokProcessBuilder::class, $processBuilder, 'Bind ' . NgrokProcessBuilder::class);

        $webService = app(NgrokWebService::class);
        $this->assertInstanceOf(NgrokWebService::class, $webService, 'Bind ' . NgrokWebService::class);
    }
}
