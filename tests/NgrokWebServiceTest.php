<?php

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use JnJairo\Laravel\Ngrok\NgrokWebService;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Message\StreamInterface;

uses(ProphecyTrait::class);

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

$json = json_encode(['tunnels' => $tunnels]);
$emptyJson = json_encode(['tunnels' => []]);

$dataset = [
    'valid' => [
        $json,
        $tunnels,
    ],
    'empty' => [
        $emptyJson,
        [],
    ],
    'invalid' => [
        '',
        [],
    ],
];

it('can get tunnels', function (
    string $json,
    array $tunnels
) {
    /**
     * @var \Prophecy\Prophecy\ObjectProphecy<\Psr\Http\Message\StreamInterface> $stream
     */
    $stream = prophesize(StreamInterface::class);
    $stream->__toString()->willReturn($json)->shouldBeCalled();

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy<\GuzzleHttp\Psr7\Response> $response
     */
    $response = prophesize(Response::class);
    $response->getBody()->willReturn($stream->reveal())->shouldBeCalled();

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy<\GuzzleHttp\Client> $httpClient
     */
    $httpClient = prophesize(Client::class);
    $httpClient->request(
        'GET',
        'http://127.0.0.1:4040/api/tunnels'
    )->willReturn(
        $response->reveal()
    )->shouldBeCalled();

    $webService = new NgrokWebService($httpClient->reveal());
    expect($webService->getTunnels())
        ->toBe($tunnels);
})->with($dataset);
