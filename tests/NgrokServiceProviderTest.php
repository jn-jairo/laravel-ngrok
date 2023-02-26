<?php

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\UrlGenerator;
use JnJairo\Laravel\Ngrok\NgrokCommand;
use JnJairo\Laravel\Ngrok\NgrokProcessBuilder;
use JnJairo\Laravel\Ngrok\NgrokServiceProvider;
use JnJairo\Laravel\Ngrok\NgrokWebService;
use Prophecy\PhpUnit\ProphecyTrait;

uses(ProphecyTrait::class);

$datasetValidNgrokUrl = [
    'http_ngrok_2' => [
        'http',
        [
            'HTTP_X_ORIGINAL_HOST' => '0000-0000.ngrok.io',
        ],
    ],
    'http_ngrok_3' => [
        'http',
        [
            'HTTP_X_FORWARDED_HOST' => '0000-0000.ngrok.io',
        ],
    ],
    'https_ngrok_2' => [
        'https',
        [
            'HTTP_X_ORIGINAL_HOST' => '0000-0000.ngrok.io',
            'HTTP_X_FORWARDED_PROTO' => 'https',
        ],
    ],
    'https_ngrok_3' => [
        'https',
        [
            'HTTP_X_FORWARDED_HOST' => '0000-0000.ngrok.io',
            'HTTP_X_FORWARDED_PROTO' => 'https',
        ],
    ],
];

$datasetInvalidNgrokUrl = [
    'http_empty' => [
        'http',
        [],
    ],
    'https_empty' => [
        'https',
        [],
    ],
    'http_ngrok_2_domain_top_level' => [
        'http',
        [
            'HTTP_X_ORIGINAL_HOST' => '0000-0000.ngrok.com',
        ],
    ],
    'http_ngrok_2_domain' => [
        'http',
        [
            'HTTP_X_ORIGINAL_HOST' => '0000-0000.notngrok.io',
        ],
    ],
    'http_ngrok_2_subdomain' => [
        'http',
        [
            'HTTP_X_ORIGINAL_HOST' => '0000_0000.ngrok.io',
        ],
    ],
    'http_ngrok_3_domain_top_level' => [
        'http',
        [
            'HTTP_X_FORWARDED_HOST' => '0000-0000.ngrok.com',
        ],
    ],
    'http_ngrok_3_domain' => [
        'http',
        [
            'HTTP_X_FORWARDED_HOST' => '0000-0000.notngrok.io',
        ],
    ],
    'http_ngrok_3_subdomain' => [
        'http',
        [
            'HTTP_X_FORWARDED_HOST' => '0000_0000.ngrok.io',
        ],
    ],
    'https_ngrok_2_domain_top_level' => [
        'https',
        [
            'HTTP_X_ORIGINAL_HOST' => '0000-0000.ngrok.com',
            'HTTP_X_FORWARDED_PROTO' => 'https',
        ],
    ],
    'https_ngrok_2_domain' => [
        'https',
        [
            'HTTP_X_ORIGINAL_HOST' => '0000-0000.notngrok.io',
            'HTTP_X_FORWARDED_PROTO' => 'https',
        ],
    ],
    'https_ngrok_2_subdomain' => [
        'https',
        [
            'HTTP_X_ORIGINAL_HOST' => '0000_0000.ngrok.io',
            'HTTP_X_FORWARDED_PROTO' => 'https',
        ],
    ],
    'https_ngrok_3_domain_top_level' => [
        'https',
        [
            'HTTP_X_FORWARDED_HOST' => '0000-0000.ngrok.com',
            'HTTP_X_FORWARDED_PROTO' => 'https',
        ],
    ],
    'https_ngrok_3_domain' => [
        'https',
        [
            'HTTP_X_FORWARDED_HOST' => '0000-0000.notngrok.io',
            'HTTP_X_FORWARDED_PROTO' => 'https',
        ],
    ],
    'https_ngrok_3_subdomain' => [
        'https',
        [
            'HTTP_X_FORWARDED_HOST' => '0000_0000.ngrok.io',
            'HTTP_X_FORWARDED_PROTO' => 'https',
        ],
    ],
];

it('has registered the bindings', function () {
    expect(app(NgrokProcessBuilder::class))
        ->toBeInstanceOf(NgrokProcessBuilder::class);

    expect(app(NgrokWebService::class))
        ->toBeInstanceOf(NgrokWebService::class);
});

it('has registered the command', function () {
    expect(app(NgrokCommand::class))
        ->toBeInstanceOf(NgrokCommand::class);

    artisan('ngrok', ['--help'])->assertExitCode(0);
});

it('does not run in console', function () {
    /**
     * @var \Prophecy\Prophecy\ObjectProphecy<\Illuminate\Contracts\Foundation\Application> $app
     */
    $app = prophesize(Application::class);
    $app->runningInConsole()->willReturn(true)->shouldBeCalled();
    $app->make('url')->shouldNotBeCalled();
    $app->make('request')->shouldNotBeCalled();

    $serviceProvider = new NgrokServiceProvider($app->reveal());
    $serviceProvider->boot();
});

it('does not setup invalid ngrok url', function (
    string $scheme,
    array $headers
) {
    /**
     * @var \Prophecy\Prophecy\ObjectProphecy<\Illuminate\Routing\UrlGenerator> $urlGenerator
     */
    $urlGenerator = prophesize(UrlGenerator::class);
    $urlGenerator->forceScheme(\Prophecy\Argument::any())->shouldNotBeCalled();
    $urlGenerator->forceRootUrl(\Prophecy\Argument::any())->shouldNotBeCalled();

    $request = Request::create(
        $scheme . '://example.com/foo',
        'GET',
        ['foo' => 'bar'],
        [],
        [],
        $headers,
    );

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy<\Illuminate\Contracts\Foundation\Application> $app
     */
    $app = prophesize(Application::class);
    $app->runningInConsole()->willReturn(false)->shouldBeCalled();
    $app->make('url')->willReturn($urlGenerator->reveal())->shouldBeCalled();
    $app->make('request')->willReturn($request)->shouldBeCalled();

    $serviceProvider = new NgrokServiceProvider($app->reveal());
    $serviceProvider->boot();

    expect(Paginator::resolveCurrentPath())
        ->not->toContain('ngrok');
})->with($datasetInvalidNgrokUrl);

it('setup valid ngrok url', function (
    string $scheme,
    array $headers
) {
    /**
     * @var \Prophecy\Prophecy\ObjectProphecy<\Illuminate\Routing\UrlGenerator> $urlGenerator
     */
    $urlGenerator = prophesize(UrlGenerator::class);
    $urlGenerator->forceScheme($scheme)->shouldBeCalled();
    $urlGenerator->forceRootUrl($scheme . '://0000-0000.ngrok.io')->shouldBeCalled();
    $urlGenerator->to(
        'foo',
        \Prophecy\Argument::any(),
        \Prophecy\Argument::any()
    )->willReturn($scheme . '://0000-0000.ngrok.io/foo')->shouldBeCalled();

    $request = Request::create(
        $scheme . '://example.com/foo',
        'GET',
        ['foo' => 'bar'],
        [],
        [],
        $headers,
    );

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy<\Illuminate\Contracts\Foundation\Application> $app
     */
    $app = prophesize(Application::class);
    $app->runningInConsole()->willReturn(false)->shouldBeCalled();
    $app->make('url')->willReturn($urlGenerator->reveal())->shouldBeCalled();
    $app->make('request')->willReturn($request)->shouldBeCalled();

    $serviceProvider = new NgrokServiceProvider($app->reveal());
    $serviceProvider->boot();

    expect(Paginator::resolveCurrentPath())
        ->toBe($scheme . '://0000-0000.ngrok.io/foo');
})->with($datasetValidNgrokUrl);
