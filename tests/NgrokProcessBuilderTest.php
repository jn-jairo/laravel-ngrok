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
    public function buildProcessProvider() : array
    {
        return [
            'default' => [
                [],
                '\'ngrok\' \'http\' \'--log\' \'stdout\' \'80\'',
            ],
            'host_header' => [
                ['example.com'],
                '\'ngrok\' \'http\' \'--log\' \'stdout\' \'--host-header\' \'example.com\' \'80\'',
            ],
            'host_header_port' => [
                ['example.com', '8000'],
                '\'ngrok\' \'http\' \'--log\' \'stdout\' \'--host-header\' \'example.com\' \'8000\'',
            ],
            'host_header_port_host' => [
                ['example.com', '8000', 'nginx'],
                '\'ngrok\' \'http\' \'--log\' \'stdout\' \'--host-header\' \'example.com\' \'nginx:8000\'',
            ],
            'host_header_empty_port' => [
                ['example.com', ''],
                '\'ngrok\' \'http\' \'--log\' \'stdout\' \'--host-header\' \'example.com\' \'80\'',
            ],
            'host_header_empty_port_empty_host' => [
                ['example.com', '', ''],
                '\'ngrok\' \'http\' \'--log\' \'stdout\' \'--host-header\' \'example.com\' \'80\'',
            ],
            'host_header_empty_port_host' => [
                ['example.com', '', 'nginx'],
                '\'ngrok\' \'http\' \'--log\' \'stdout\' \'--host-header\' \'example.com\' \'nginx:80\'',
            ],
            'empty_host_header_emtpy_port_host' => [
                ['', '', 'nginx'],
                '\'ngrok\' \'http\' \'--log\' \'stdout\' \'nginx:80\'',
            ],
            'empty_host_header' => [
                [''],
                '\'ngrok\' \'http\' \'--log\' \'stdout\' \'80\'',
            ],
            'empty_host_header_emtpy_port' => [
                ['', ''],
                '\'ngrok\' \'http\' \'--log\' \'stdout\' \'80\'',
            ],
            'empty_host_header_emtpy_port_empty_host' => [
                ['', '', ''],
                '\'ngrok\' \'http\' \'--log\' \'stdout\' \'80\'',
            ],
            'extra_single' => [
                ['example.com', '', '', ['--region=eu']],
                '\'ngrok\' \'http\' \'--log\' \'stdout\' \'--region=eu\' \'--host-header\' \'example.com\' \'80\'',
            ],
            'extra_multiple' => [
                ['example.com', '', '', ['--region=eu', '--config=../ngrok.yml']],
                '\'ngrok\' \'http\' \'--log\' \'stdout\' \'--region=eu\' \'--config=../ngrok.yml\' \'--host-header\' \'example.com\' \'80\'',
            ],
        ];
    }

    /**
     * @dataProvider buildProcessProvider
     */
    public function test_build_process(array $args, string $command) : void
    {
        $processBuilder = new NgrokProcessBuilder(__DIR__);
        $process = $processBuilder->buildProcess(...$args);

        $this->assertInstanceOf(Process::class, $process);

        $this->assertSame($command, $process->getCommandLine());
        $this->assertSame(__DIR__, $process->getWorkingDirectory());
        $this->assertNull($process->getTimeout());
    }
}
