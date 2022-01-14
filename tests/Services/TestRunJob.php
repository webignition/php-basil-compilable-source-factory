<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

class TestRunJob
{
    private int $exitCode;
    private int $expectedExitCode;

    /**
     * @var array<string>
     */
    private array $output;

    public function __construct(
        private string $path
    ) {
        $this->exitCode = -1;
        $this->expectedExitCode = 0;
        $this->output = [];
    }

    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param array<string> $output
     */
    public function setOutput(array $output): void
    {
        $this->output = $output;
    }

    /**
     * @return array<string>
     */
    public function getOutput(): array
    {
        return $this->output;
    }

    public function getOutputAsString(): string
    {
        return implode("\n", $this->output);
    }

    public function setExpectedExitCode(int $expectedExitCode): void
    {
        $this->expectedExitCode = $expectedExitCode;
    }

    public function getExpectedExitCode(): int
    {
        return $this->expectedExitCode;
    }

    public function setExitCode(int $exitCode): void
    {
        $this->exitCode = $exitCode;
    }

    public function getExitCode(): int
    {
        return $this->exitCode;
    }
}
