<?php

namespace JnJairo\Laravel\Ngrok;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\ServiceProvider;
use JnJairo\Laravel\Ngrok\NgrokCommand;
use JnJairo\Laravel\Ngrok\NgrokProcessBuilder;
use JnJairo\Laravel\Ngrok\NgrokWebService;

class NgrokServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot() : void
    {
        if (! $this->app->runningInConsole()) {
            $urlGenerator = $this->app->make('url');
            $request = $this->app->make('request');

            $this->forceNgrokSchemeHost($urlGenerator, $request);
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register() : void
    {
        $this->app->bind(NgrokProcessBuilder::class, function ($app) {
            return new NgrokProcessBuilder($app->basePath());
        });

        $this->app->bind(NgrokWebService::class, function () {
            return new NgrokWebService(new \GuzzleHttp\Client());
        });

        $this->commands([
           NgrokCommand::class,
        ]);
    }

    /**
     * Force the url generator to the ngrok scheme://host.
     *
     * @param \Illuminate\Routing\UrlGenerator $urlGenerator
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    private function forceNgrokSchemeHost(UrlGenerator $urlGenerator, Request $request) : void
    {
        $host = $this->extractOriginalHost($request);

        if ($this->isNgrokHost($host)) {
            $scheme = $this->extractOriginalScheme($request);

            $urlGenerator->forceScheme($scheme);
            $urlGenerator->forceRootUrl($scheme . '://' . $host);

            Paginator::currentPathResolver(function () use ($urlGenerator, $request) {
                return $urlGenerator->to($request->path());
            });
        }
    }

    /**
     * Extract the original scheme from the request.
     *
     * @param \Illuminate\Http\Request $request
     * @return string
     */
    private function extractOriginalScheme(Request $request) : string
    {
        if ($request->hasHeader('x-forwarded-proto')) {
            $scheme = $request->header('x-forwarded-proto');
        } else {
            $scheme = $request->getScheme();
        }

        return $scheme;
    }

    /**
     * Extract the original host from the request.
     *
     * @param \Illuminate\Http\Request $request
     * @return string
     */
    private function extractOriginalHost(Request $request) : string
    {
        if ($request->hasHeader('x-original-host')) {
            $host = $request->header('x-original-host');
        } else {
            $host = $request->getHost();
        }

        return $host;
    }

    /**
     * Check if the host from ngrok.
     *
     * @param string $host
     * @return bool
     */
    private function isNgrokHost(string $host) : bool
    {
        return preg_match('/^[a-z0-9]+\.ngrok\.io$/i', $host);
    }
}
