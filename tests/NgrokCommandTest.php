<?php

use JnJairo\Laravel\Ngrok\NgrokProcessBuilder;
use JnJairo\Laravel\Ngrok\NgrokWebService;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Process\Process;

uses(ProphecyTrait::class);

$datasetValid = [
    'basic' => [
        ['app.url' => ''],
        [
            'host-header' => 'example.com',
            '--host' => 'localhost',
            '--port' => '80',
        ],
        [
            'example.com',
            '80',
            'localhost',
            [],
        ],
        [
            'Host header: example.com',
            'Host: localhost',
            'Port: 80',
        ],
    ],
    'extra' => [
        ['app.url' => ''],
        [
            'host-header' => 'example.com',
            '--host' => 'nginx',
            '--port' => '80',
            '--extra' => ['--region=eu', '--config=ngrok.yml'],
        ],
        [
            'example.com',
            '80',
            'nginx',
            ['--region=eu', '--config=ngrok.yml'],
        ],
        [
            'Host header: example.com',
            'Host: nginx',
            'Port: 80',
            'Extra: --region=eu --config=ngrok.yml',
        ],
    ],
    'config' => [
        ['app.url' => 'http://example.com:8000'],
        [],
        [
            'example.com',
            '8000',
            'localhost',
            [],
        ],
        [
            'Host header: example.com',
            'Host: localhost',
            'Port: 8000',
        ],
    ],
];

$datasetInvalid = [
    'invalid' => [
        ['app.url' => ''],
        [],
        [
            'example.com',
            '8000',
            'localhost',
            [],
        ],
        [],
    ],
];

it('works', function (
    array $config,
    array $params,
    array $expectedArguments,
    array $expectedOutputs
) {
    config($config);

    $port = $expectedArguments[1] ?: '80';
    $host = $expectedArguments[2] ?: 'localhost';

    $tunnels = [
        [
            'public_url' => 'http://0000-0000.ngrok.io',
            'config' => ['addr' => $host . ':' . $port],
        ],
        [
            'public_url' => 'https://0000-0000.ngrok.io',
            'config' => ['addr' => $host . ':' . $port],
        ],
    ];

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy<\JnJairo\Laravel\Ngrok\NgrokWebService> $webService
     */
    $webService = prophesize(NgrokWebService::class);
    $webService->setUrl('http://127.0.0.1:4040')->shouldBeCalled();
    $webService->getTunnels()->willReturn($tunnels)->shouldBeCalled();

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy<\Symfony\Component\Process\Process> $process
     */
    $process = prophesize(Process::class);
    $process->run(\Prophecy\Argument::type('callable'))->will(function ($args) use ($process) {
        $callback = $args[0];

        $process->getOutput()->willReturn('msg="starting web service" addr=127.0.0.1:4040')->shouldBeCalled();
        $process->clearOutput()->willReturn($process)->shouldBeCalled();

        $callback(Process::OUT, 'msg="starting web service" addr=127.0.0.1:4040');

        $process->clearErrorOutput()->willReturn($process)->shouldBeCalled();

        $callback(Process::ERR, 'error');

        return 0;
    })->shouldBeCalled();
    $process->getErrorOutput()->willReturn('')->shouldBeCalled();
    $process->getExitCode()->willReturn(0)->shouldBeCalled();

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy<\JnJairo\Laravel\Ngrok\NgrokProcessBuilder> $processBuilder
     */
    $processBuilder = prophesize(NgrokProcessBuilder::class);
    $processBuilder
        ->buildProcess(...$expectedArguments)
        ->willReturn($process->reveal())
        ->shouldBeCalled();

    instance(NgrokWebService::class, $webService->reveal());
    instance(NgrokProcessBuilder::class, $processBuilder->reveal());

    $command = artisan('ngrok', $params);

    foreach ($expectedOutputs as $output) {
        $command = $command->expectsOutput($output);
    }

    $command->assertExitCode(0);
})->with($datasetValid);

it('fails', function (
    array $config,
    array $params,
    array $expectedArguments,
    array $expectedOutputs
) {
    config($config);

    $port = $expectedArguments[1] ?: '80';
    $host = $expectedArguments[2] ?: 'localhost';

    $tunnels = [
        [
            'public_url' => 'http://0000-0000.ngrok.io',
            'config' => ['addr' => $host . ':' . $port],
        ],
        [
            'public_url' => 'https://0000-0000.ngrok.io',
            'config' => ['addr' => $host . ':' . $port],
        ],
    ];

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy<\JnJairo\Laravel\Ngrok\NgrokWebService> $webService
     */
    $webService = prophesize(NgrokWebService::class);
    $webService->setUrl('http://127.0.0.1:4040')->shouldNotBeCalled();
    $webService->getTunnels()->willReturn($tunnels)->shouldNotBeCalled();

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy<\Symfony\Component\Process\Process> $process
     */
    $process = prophesize(Process::class);
    $process->run(\Prophecy\Argument::type('callable'))->shouldNotBeCalled();
    $process->getErrorOutput()->willReturn('')->shouldNotBeCalled();
    $process->getExitCode()->willReturn(0)->shouldNotBeCalled();

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy<\JnJairo\Laravel\Ngrok\NgrokProcessBuilder> $processBuilder
     */
    $processBuilder = prophesize(NgrokProcessBuilder::class);
    $processBuilder
        ->buildProcess(...$expectedArguments)
        ->willReturn($process->reveal())
        ->shouldNotBeCalled();

    instance(NgrokWebService::class, $webService->reveal());
    instance(NgrokProcessBuilder::class, $processBuilder->reveal());

    $command = artisan('ngrok', $params);

    foreach ($expectedOutputs as $output) {
        $command = $command->expectsOutput($output);
    }

    $command->assertExitCode(1);
})->with($datasetInvalid);
