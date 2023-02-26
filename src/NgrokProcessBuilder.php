<?php

namespace JnJairo\Laravel\Ngrok;

use Symfony\Component\Process\Process;

class NgrokProcessBuilder
{
    /**
     * Current working directory.
     *
     * @var ?string
     */
    protected $cwd;

    /**
     * @param string $cwd
     */
    public function __construct(string $cwd = null)
    {
        $this->setWorkingDirectory($cwd);
    }

    /**
     * Set the current working directory.
     *
     * @param string $cwd
     */
    public function setWorkingDirectory(string $cwd = null): void
    {
        $this->cwd = $cwd;
    }

    /**
     * Get the current working directory.
     *
     * @return string
     */
    public function getWorkingDirectory(): ?string
    {
        return $this->cwd;
    }

    /**
     * Build ngrok command.
     *
     * @param string $hostHeader
     * @param string $port
     * @param string $host
     * @param array<int, string> $extra
     * @return \Symfony\Component\Process\Process
     */
    public function buildProcess(
        string $hostHeader = '',
        string $port = '80',
        string $host = '',
        array $extra = []
    ): Process {
        $command = ['ngrok', 'http', '--log', 'stdout'];

        $command = array_merge($command, $extra);

        if ($hostHeader !== '') {
            $command[] = '--host-header';
            $command[] = $hostHeader;
        }

        if ($host !== '') {
            $command[] = $host . ':' . ($port ?: '80');
        } else {
            $command[] = $port ?: '80';
        }

        return new Process($command, $this->getWorkingDirectory(), null, null, null);
    }
}
