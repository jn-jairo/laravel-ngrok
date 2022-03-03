<?php

namespace JnJairo\Laravel\Ngrok\Tests;

use JnJairo\Laravel\Ngrok\NgrokProcessBuilder;
use JnJairo\Laravel\Ngrok\NgrokWebService;
use JnJairo\Laravel\Ngrok\Tests\OrchestraTestCase as TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Process\Process;

/**
 * @testdox Ngrok command
 */
class NgrokCommandTest extends TestCase
{
    use ProphecyTrait;

    public function test_handle() : void
    {
        $hostHeader = 'example.com';
        $port = '80';
        $host = 'localhost';
        $extra = [];

        config(['app.url' => '']);

        $tunnels = [
            [
                'public_url' => 'http://0000-0000.ngrok.io',
                'config' => ['addr' => 'localhost:80'],
            ],
            [
                'public_url' => 'https://0000-0000.ngrok.io',
                'config' => ['addr' => 'localhost:80'],
            ],
        ];

        $webService = $this->prophesize(NgrokWebService::class);
        $webService->setUrl('http://127.0.0.1:4040')->shouldBeCalled();
        $webService->getTunnels()->willReturn($tunnels)->shouldBeCalled();

        $process = $this->prophesize(Process::class);
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

        $processBuilder = $this->prophesize(NgrokProcessBuilder::class);
        $processBuilder->buildProcess($hostHeader, $port, $host, $extra)->willReturn($process->reveal())->shouldBeCalled();

        app()->instance(NgrokWebService::class, $webService->reveal());
        app()->instance(NgrokProcessBuilder::class, $processBuilder->reveal());

        $this->artisan('ngrok', [
            'host-header' => $hostHeader,
            '--host' => $host,
            '--port' => $port,
        ])
             ->expectsOutput('Host header: ' . $hostHeader)
             ->expectsOutput('Host: ' . $host)
             ->expectsOutput('Port: ' . $port)
             ->assertExitCode(0);
    }

    public function test_handle_extra() : void
    {
        $hostHeader = 'example.com';
        $port = '80';
        $host = 'nginx';
        $extra = ['--region=eu', '--config=ngrok.yml'];
        $extraString = '--region=eu --config=ngrok.yml';

        config(['app.url' => '']);

        $tunnels = [
            [
                'public_url' => 'http://0000-0000.ngrok.io',
                'config' => ['addr' => 'nginx:80'],
            ],
            [
                'public_url' => 'https://0000-0000.ngrok.io',
                'config' => ['addr' => 'nginx:80'],
            ],
        ];

        $webService = $this->prophesize(NgrokWebService::class);
        $webService->setUrl('http://127.0.0.1:4040')->shouldBeCalled();
        $webService->getTunnels()->willReturn($tunnels)->shouldBeCalled();

        $process = $this->prophesize(Process::class);
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

        $processBuilder = $this->prophesize(NgrokProcessBuilder::class);
        $processBuilder->buildProcess($hostHeader, $port, $host, $extra)->willReturn($process->reveal())->shouldBeCalled();

        app()->instance(NgrokWebService::class, $webService->reveal());
        app()->instance(NgrokProcessBuilder::class, $processBuilder->reveal());

        $this->artisan('ngrok', [
            'host-header' => $hostHeader,
            '--host' => $host,
            '--port' => $port,
            '--extra' => $extra,
        ])
             ->expectsOutput('Host header: ' . $hostHeader)
             ->expectsOutput('Host: ' . $host)
             ->expectsOutput('Port: ' . $port)
             ->expectsOutput('Extra: ' . $extraString)
             ->assertExitCode(0);
    }

    public function test_handle_from_config() : void
    {
        $hostHeader = 'example.com';
        $port = '8000';
        $host = 'localhost';
        $extra = [];

        config(['app.url' => 'http://example.com:8000']);

        $tunnels = [
            [
                'public_url' => 'http://0000-0000.ngrok.io',
                'config' => ['addr' => 'localhost:8000'],
            ],
            [
                'public_url' => 'https://0000-0000.ngrok.io',
                'config' => ['addr' => 'localhost:8000'],
            ],
        ];

        $webService = $this->prophesize(NgrokWebService::class);
        $webService->setUrl('http://127.0.0.1:4040')->shouldBeCalled();
        $webService->getTunnels()->willReturn($tunnels)->shouldBeCalled();

        $process = $this->prophesize(Process::class);
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

        $processBuilder = $this->prophesize(NgrokProcessBuilder::class);
        $processBuilder->buildProcess($hostHeader, $port, $host, $extra)->willReturn($process->reveal())->shouldBeCalled();

        app()->instance(NgrokWebService::class, $webService->reveal());
        app()->instance(NgrokProcessBuilder::class, $processBuilder->reveal());

        $this->artisan('ngrok')
             ->expectsOutput('Host header: ' . $hostHeader)
             ->expectsOutput('Host: ' . $host)
             ->expectsOutput('Port: ' . $port)
             ->assertExitCode(0);
    }

    public function test_handle_invalid_host_header() : void
    {
        $hostHeader = 'example.com';
        $port = '8000';
        $host = 'localhost';
        $extra = [];

        config(['app.url' => '']);

        $tunnels = [
            [
                'public_url' => 'http://0000-0000.ngrok.io',
                'config' => ['addr' => 'localhost:8000'],
            ],
            [
                'public_url' => 'https://0000-0000.ngrok.io',
                'config' => ['addr' => 'localhost:8000'],
            ],
        ];

        $webService = $this->prophesize(NgrokWebService::class);
        $webService->setUrl('http://127.0.0.1:4040')->shouldNotBeCalled();
        $webService->getTunnels()->willReturn($tunnels)->shouldNotBeCalled();

        $process = $this->prophesize(Process::class);
        $process->run(\Prophecy\Argument::type('callable'))->shouldNotBeCalled();
        $process->getErrorOutput()->willReturn('')->shouldNotBeCalled();
        $process->getExitCode()->willReturn(0)->shouldNotBeCalled();

        $processBuilder = $this->prophesize(NgrokProcessBuilder::class);
        $processBuilder->buildProcess($hostHeader, $port, $host, $extra)->willReturn($process->reveal())->shouldNotBeCalled();

        app()->instance(NgrokWebService::class, $webService->reveal());
        app()->instance(NgrokProcessBuilder::class, $processBuilder->reveal());

        $this->artisan('ngrok')
             ->assertExitCode(1);
    }
}
