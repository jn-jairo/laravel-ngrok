<?php

namespace JnJairo\Laravel\Ngrok\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use JnJairo\Laravel\Ngrok\NgrokWebService;
use JnJairo\Laravel\Ngrok\Tests\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Message\StreamInterface;

/**
 * @testdox Ngrok web service
 */
class NgrokWebServiceTest extends TestCase
{
    use ProphecyTrait;

    public function test_get_tunnels() : void
    {
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

        $stream = $this->prophesize(StreamInterface::class);
        $stream->__toString()->willReturn(json_encode(['tunnels' => $tunnels]))->shouldBeCalled();

        $response = $this->prophesize(Response::class);
        $response->getBody()->willReturn($stream->reveal())->shouldBeCalled();

        $httpClient = $this->prophesize(Client::class);
        $httpClient->request(
            'GET',
            'http://127.0.0.1:4040/api/tunnels'
        )->willReturn(
            $response->reveal()
        )->shouldBeCalled();

        $webService = new NgrokWebService($httpClient->reveal());
        $this->assertSame($tunnels, $webService->getTunnels());
    }

    public function test_get_tunnels_empty() : void
    {
        $tunnels = [];

        $stream = $this->prophesize(StreamInterface::class);
        $stream->__toString()->willReturn(json_encode(['tunnels' => $tunnels]))->shouldBeCalled();

        $response = $this->prophesize(Response::class);
        $response->getBody()->willReturn($stream->reveal())->shouldBeCalled();

        $httpClient = $this->prophesize(Client::class);
        $httpClient->request(
            'GET',
            'http://127.0.0.1:4040/api/tunnels'
        )->willReturn(
            $response->reveal()
        )->shouldBeCalled();

        $webService = new NgrokWebService($httpClient->reveal());
        $this->assertSame($tunnels, $webService->getTunnels());
    }

    public function test_get_tunnels_invalid_json() : void
    {
        $tunnels = [];

        $stream = $this->prophesize(StreamInterface::class);
        $stream->__toString()->willReturn('')->shouldBeCalled();

        $response = $this->prophesize(Response::class);
        $response->getBody()->willReturn($stream->reveal())->shouldBeCalled();

        $httpClient = $this->prophesize(Client::class);
        $httpClient->request(
            'GET',
            'http://127.0.0.1:4040/api/tunnels'
        )->willReturn(
            $response->reveal()
        )->shouldBeCalled();

        $webService = new NgrokWebService($httpClient->reveal());
        $this->assertSame($tunnels, $webService->getTunnels());
    }
}
