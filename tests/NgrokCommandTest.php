<?php

namespace JnJairo\Laravel\Ngrok\Tests;

use Illuminate\Console\OutputStyle;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use JnJairo\Laravel\Ngrok\NgrokCommand;
use JnJairo\Laravel\Ngrok\NgrokProcessBuilder;
use JnJairo\Laravel\Ngrok\NgrokWebService;
use JnJairo\Laravel\Ngrok\Tests\TestCase;
use Symfony\Component\Process\Process;

/**
 * @testdox Ngrok command
 */
class NgrokCommandTest extends TestCase
{
    public function test_handle() : void
    {
        $input = new \Symfony\Component\Console\Input\ArrayInput(['host' => 'example.com', '--port' => '80']);
        $output = new \Symfony\Component\Console\Output\NullOutput;

        $config = $this->prophesize(Repository::class);
        $config->get('app.url')->willReturn('http://example.com')->shouldNotBeCalled();

        $container = $this->prophesize(Container::class);
        $container->make(OutputStyle::class, \Prophecy\Argument::any())->willReturn(
            new OutputStyle($input, $output)
        );
        $container->call(\Prophecy\Argument::any())->shouldBeCalled();
        $container->make('config')->willReturn($config->reveal())->shouldNotBeCalled();

        $tunnels = [
            [
                'public_url' => 'http://00000000.ngrok.io',
                'config' => ['addr' => 'localhost:80'],
            ],
            [
                'public_url' => 'https://00000000.ngrok.io',
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
            $process->clearOutput()->shouldBeCalled();

            $callback(Process::OUT, 'msg="starting web service" addr=127.0.0.1:4040');

            $process->clearErrorOutput()->shouldBeCalled();

            $callback(Process::ERR, 'error');

            return 0;
        })->shouldBeCalled();
        $process->getErrorOutput()->willReturn('')->shouldBeCalled();
        $process->getExitCode()->willReturn(0)->shouldBeCalled();

        $processBuilder = $this->prophesize(NgrokProcessBuilder::class);
        $processBuilder->buildProcess('example.com', '80')->willReturn($process->reveal())->shouldBeCalled();

        $command = new NgrokCommand($processBuilder->reveal(), $webService->reveal());
        $command->setLaravel($container->reveal());
        $command->run($input, $output);
        $this->assertSame(0, $command->handle());

        $container->call([$command, 'handle'])->shouldHaveBeenCalled();
    }

    public function test_handle_from_config() : void
    {
        $input = new \Symfony\Component\Console\Input\ArrayInput([]);
        $output = new \Symfony\Component\Console\Output\NullOutput;

        $config = $this->prophesize(Repository::class);
        $config->get('app.url')->willReturn('http://example.com:8000')->shouldBeCalled();

        $container = $this->prophesize(Container::class);
        $container->make(OutputStyle::class, \Prophecy\Argument::any())->willReturn(
            new OutputStyle($input, $output)
        );
        $container->call(\Prophecy\Argument::any())->shouldBeCalled();
        $container->make('config')->willReturn($config->reveal())->shouldBeCalled();

        $tunnels = [
            [
                'public_url' => 'http://00000000.ngrok.io',
                'config' => ['addr' => 'localhost:8000'],
            ],
            [
                'public_url' => 'https://00000000.ngrok.io',
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
            $process->clearOutput()->shouldBeCalled();

            $callback(Process::OUT, 'msg="starting web service" addr=127.0.0.1:4040');

            $process->clearErrorOutput()->shouldBeCalled();

            $callback(Process::ERR, 'error');

            return 0;
        })->shouldBeCalled();
        $process->getErrorOutput()->willReturn('')->shouldBeCalled();
        $process->getExitCode()->willReturn(0)->shouldBeCalled();

        $processBuilder = $this->prophesize(NgrokProcessBuilder::class);
        $processBuilder->buildProcess('example.com', '8000')->willReturn($process->reveal())->shouldBeCalled();

        $command = new NgrokCommand($processBuilder->reveal(), $webService->reveal());
        $command->setLaravel($container->reveal());
        $command->run($input, $output);
        $this->assertSame(0, $command->handle());

        $container->call([$command, 'handle'])->shouldHaveBeenCalled();
    }

    public function test_handle_invalid_host() : void
    {
        $input = new \Symfony\Component\Console\Input\ArrayInput([]);
        $output = new \Symfony\Component\Console\Output\NullOutput;

        $config = $this->prophesize(Repository::class);
        $config->get('app.url')->willReturn('')->shouldBeCalled();

        $container = $this->prophesize(Container::class);
        $container->make(OutputStyle::class, \Prophecy\Argument::any())->willReturn(
            new OutputStyle($input, $output)
        );
        $container->call(\Prophecy\Argument::any())->shouldBeCalled();
        $container->make('config')->willReturn($config->reveal())->shouldBeCalled();

        $tunnels = [
            [
                'public_url' => 'http://00000000.ngrok.io',
                'config' => ['addr' => 'localhost:8000'],
            ],
            [
                'public_url' => 'https://00000000.ngrok.io',
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
        $processBuilder->buildProcess('example.com', '8000')->willReturn($process->reveal())->shouldNotBeCalled();

        $command = new NgrokCommand($processBuilder->reveal(), $webService->reveal());
        $command->setLaravel($container->reveal());
        $command->run($input, $output);
        $this->assertSame(1, $command->handle());

        $container->call([$command, 'handle'])->shouldHaveBeenCalled();
    }
}
