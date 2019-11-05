<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

class TestRunJob
{
    private $path = '';
    private $exitCode = -1;
    private $expectedExitCode = 0;
    private $output = [];

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setOutput(array $output): void
    {
        $this->output = $output;
    }

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
