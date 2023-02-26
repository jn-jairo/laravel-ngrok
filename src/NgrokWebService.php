<?php

namespace JnJairo\Laravel\Ngrok;

use GuzzleHttp\Client;

class NgrokWebService
{
    /**
     * The web service url.
     *
     * @var string
     */
    protected $url;

    /**
     * Http client.
     *
     * @var \GuzzleHttp\Client
     */
    protected $httpClient;

    /**
     * @param \GuzzleHttp\Client $httpClient
     * @param string $url
     */
    public function __construct(Client $httpClient, string $url = 'http://127.0.0.1:4040')
    {
        $this->setHttpClient($httpClient);
        $this->setUrl($url);
    }

    /**
     * Set the web service url.
     *
     * @param string $url
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * Get the web service url.
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Set the http client.
     *
     * @param \GuzzleHttp\Client $httpClient
     */
    public function setHttpClient(Client $httpClient): void
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Get the http client.
     *
     * @return \GuzzleHttp\Client
     */
    public function getHttpClient(): Client
    {
        return $this->httpClient;
    }

    /**
     * Request the tunnels.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getTunnels(): array
    {
        $tunnels = [];

        $response = json_decode(
            $this->getHttpClient()->request(
                'GET',
                $this->getUrl() . '/api/tunnels'
            )->getBody(),
            true
        );

        if ($response !== false && isset($response['tunnels']) && ! empty($response['tunnels'])) {
            /**
             * @var array<int, array<string, mixed>> $tunnels
             */
            $tunnels = $response['tunnels'];
        }

        return $tunnels;
    }
}
