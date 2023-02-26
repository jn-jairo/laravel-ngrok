<?php

namespace JnJairo\Laravel\Ngrok;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class NgrokCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ngrok
                            {host-header? : Host header to identify the app (Example: myapp.test)}
                            {--H|host= : Host to tunnel the requests (default: localhost)}
                            {--P|port= : Port to tunnel the requests (default: 80)}
                            {--E|extra=* : Extra arguments to ngrok command}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Share the application with ngrok';

    /**
     * Process builder.
     *
     * @var \JnJairo\Laravel\Ngrok\NgrokProcessBuilder
     */
    protected $processBuilder;

    /**
     * Web service.
     *
     * @var \JnJairo\Laravel\Ngrok\NgrokWebService
     */
    protected $webService;

    /**
     * @param \JnJairo\Laravel\Ngrok\NgrokProcessBuilder $processBuilder
     * @param \JnJairo\Laravel\Ngrok\NgrokWebService $webService
     */
    public function __construct(NgrokProcessBuilder $processBuilder, NgrokWebService $webService)
    {
        parent::__construct();

        $this->processBuilder = $processBuilder;
        $this->webService = $webService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        /**
         * @var string|null $hostHeader
         */
        $hostHeader = $this->argument('host-header');
        /**
         * @var string|null $host
         */
        $host = $this->option('host');
        /**
         * @var string|null $port
         */
        $port = $this->option('port');
        /**
         * @var array<int, string> $extra
         */
        $extra = $this->option('extra');

        if ($hostHeader === null) {
            /**
             * @var \Illuminate\Config\Repository $config
             */
            $config = $this->getLaravel()->make('config');
            $url = is_string($config->get('app.url')) ? $config->get('app.url') : '';

            $urlParsed = parse_url($url);

            if ($urlParsed !== false) {
                if (isset($urlParsed['host'])) {
                    $hostHeader = $urlParsed['host'];
                }

                if (isset($urlParsed['port']) && $port === null) {
                    $port = (string) $urlParsed['port'];
                }
            }
        }

        if (empty($hostHeader)) {
            $this->error('Invalid host header');
            return 1;
        }

        $host = $host ?: 'localhost';
        $port = $port ?: '80';

        $this->line('-----------------');
        $this->line('|     NGROK     |');
        $this->line('-----------------');

        $this->line('');

        $this->line('<fg=green>Host header: </fg=green>' . $hostHeader);
        $this->line('<fg=green>Host: </fg=green>' . $host);
        $this->line('<fg=green>Port: </fg=green>' . $port);

        if (! empty($extra)) {
            $this->line('<fg=green>Extra: </fg=green>' . implode(' ', $extra));
        }

        $this->line('');

        $process = $this->processBuilder->buildProcess($hostHeader, $port, $host, $extra);

        return $this->runProcess($process);
    }

    /**
     * Run the process.
     *
     * @param \Symfony\Component\Process\Process $process
     * @return int Exit code.
     */
    private function runProcess(Process $process): int
    {
        $webService = $this->webService;

        $webServiceStarted = false;
        $tunnelStarted = false;

        $process->run(function ($type, $data) use (&$process, &$webService, &$webServiceStarted, &$tunnelStarted) {
            if (! $webServiceStarted) {
                if (preg_match('/msg="starting web service".*? addr=(?<addr>\S+)/', $process->getOutput(), $matches)) {
                    $webServiceStarted = true;

                    $webServiceUrl = 'http://' . $matches['addr'];

                    $webService->setUrl($webServiceUrl);

                    $this->line('<fg=green>Web Interface: </fg=green>' . $webServiceUrl . "\n");
                }
            }

            if ($webServiceStarted && ! $tunnelStarted) {
                $tunnels = $webService->getTunnels();

                if (! empty($tunnels)) {
                    $tunnelStarted = true;

                    foreach ($tunnels as $tunnel) {
                        $this->line('<fg=green>Forwarding: </fg=green>'
                            . $tunnel['public_url'] . ' -> ' . $tunnel['config']['addr']);
                    }
                }
            }

            if (Process::OUT === $type) {
                $process->clearOutput();
            } else {
                $this->error($data);
                $process->clearErrorOutput();
            }
        });

        $this->error($process->getErrorOutput());

        return (int) $process->getExitCode();
    }
}
