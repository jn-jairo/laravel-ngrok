<?php

namespace JnJairo\Laravel\Ngrok\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use JnJairo\Laravel\Ngrok\NgrokWebService;
use JnJairo\Laravel\Ngrok\Tests\TestCase;

/**
 * @testdox Ngrok web service
 */
class NgrokWebServiceTest extends TestCase
{
    public function test_get_tunnels() : void
    {
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

        $response = $this->prophesize(Response::class);
        $response->getBody()->willReturn(json_encode(['tunnels' => $tunnels]))->shouldBeCalled();

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

        $response = $this->prophesize(Response::class);
        $response->getBody()->willReturn(json_encode(['tunnels' => $tunnels]))->shouldBeCalled();

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

        $response = $this->prophesize(Response::class);
        $response->getBody()->willReturn('')->shouldBeCalled();

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
