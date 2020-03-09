<?php

namespace JnJairo\Laravel\Ngrok\Tests;

use JnJairo\Laravel\Ngrok\NgrokProcessBuilder;
use JnJairo\Laravel\Ngrok\Tests\TestCase;
use Symfony\Component\Process\Process;

/**
 * @testdox Ngrok process builder
 */
class NgrokProcessBuilderTest extends TestCase
{
    public function test_build_process_default() : void
    {
        $processBuilder = new NgrokProcessBuilder(__DIR__);
        $process = $processBuilder->buildProcess();

        $this->assertInstanceOf(Process::class, $process);

        $this->assertSame('\'ngrok\' \'http\' \'--log\' \'stdout\' \'80\'', $process->getCommandLine());
        $this->assertSame(__DIR__, $process->getWorkingDirectory());
        $this->assertNull($process->getTimeout());
    }

    public function test_build_process_host() : void
    {
        $processBuilder = new NgrokProcessBuilder(__DIR__);
        $process = $processBuilder->buildProcess('example.com');

        $this->assertInstanceOf(Process::class, $process);

        $this->assertSame('\'ngrok\' \'http\' \'--log\' \'stdout\' \'--host-header\' \'example.com\' \'80\'', $process->getCommandLine());
        $this->assertSame(__DIR__, $process->getWorkingDirectory());
        $this->assertNull($process->getTimeout());
    }

    public function test_build_process_host_port() : void
    {
        $processBuilder = new NgrokProcessBuilder(__DIR__);
        $process = $processBuilder->buildProcess('example.com', '8000');

        $this->assertInstanceOf(Process::class, $process);

        $this->assertSame('\'ngrok\' \'http\' \'--log\' \'stdout\' \'--host-header\' \'example.com\' \'8000\'', $process->getCommandLine());
        $this->assertSame(__DIR__, $process->getWorkingDirectory());
        $this->assertNull($process->getTimeout());
    }

    public function test_build_process_host_empty_port() : void
    {
        $processBuilder = new NgrokProcessBuilder(__DIR__);
        $process = $processBuilder->buildProcess('example.com', '');

        $this->assertInstanceOf(Process::class, $process);

        $this->assertSame('\'ngrok\' \'http\' \'--log\' \'stdout\' \'--host-header\' \'example.com\' \'80\'', $process->getCommandLine());
        $this->assertSame(__DIR__, $process->getWorkingDirectory());
        $this->assertNull($process->getTimeout());
    }

    public function test_build_process_empty_host() : void
    {
        $processBuilder = new NgrokProcessBuilder(__DIR__);
        $process = $processBuilder->buildProcess('');

        $this->assertInstanceOf(Process::class, $process);

        $this->assertSame('\'ngrok\' \'http\' \'--log\' \'stdout\' \'80\'', $process->getCommandLine());
        $this->assertSame(__DIR__, $process->getWorkingDirectory());
        $this->assertNull($process->getTimeout());
    }

    public function test_build_process_empty_host_emtpy_port() : void
    {
        $processBuilder = new NgrokProcessBuilder(__DIR__);
        $process = $processBuilder->buildProcess('', '');

        $this->assertInstanceOf(Process::class, $process);

        $this->assertSame('\'ngrok\' \'http\' \'--log\' \'stdout\' \'80\'', $process->getCommandLine());
        $this->assertSame(__DIR__, $process->getWorkingDirectory());
        $this->assertNull($process->getTimeout());
    }
}
