<?php

use JnJairo\Laravel\Ngrok\NgrokProcessBuilder;
use Symfony\Component\Process\Process;

$dataset = [
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
        '\'ngrok\' \'http\' \'--log\' \'stdout\' \'--region=eu\''
        . ' \'--config=../ngrok.yml\' \'--host-header\' \'example.com\' \'80\'',
    ],
];

it('can build process', function (
    array $args,
    string $command
) {
    $processBuilder = new NgrokProcessBuilder(__DIR__);
    $process = $processBuilder->buildProcess(...$args);

    expect($process)
        ->toBeInstanceOf(Process::class);

    expect($process->getCommandLine())
        ->toBe($command);
    expect($process->getWorkingDirectory())
        ->toBe(__DIR__);
    expect($process->getTimeout())
        ->toBeNull();
})->with($dataset);
