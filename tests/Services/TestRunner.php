<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

class TestRunner
{
    private const PATH = __DIR__ . '/../Generated/';

    public function createTestRunJob(string $code, int $expectedExitCode = 0): ?TestRunJob
    {
        $content = '<?php' . "\n\n" . $code . "\n";

        $classMatches = [];
        preg_match('/Generated[a-z0-9]{32}Test/i', $content, $classMatches);
        $name = $classMatches[0] . '.php';

        $path = (string) self::PATH . $name;

        if (preg_match('/Generated\/Generated[A-Fa-f0-9]{32}Test.php/', $path)) {
            file_put_contents($path, $content);

            $testRunJob = new TestRunJob((string) realpath($path));
            $testRunJob->setExpectedExitCode($expectedExitCode);

            return $testRunJob;
        }

        return null;
    }

    public function run(TestRunJob $testRunJob): void
    {
        $path = $testRunJob->getPath();

        if (preg_match('/Generated\/Generated[A-Fa-f0-9]{32}Test.php/', $path)) {
            $command = 'vendor/bin/phpunit ' . $path;

            $output = [];
            $exitCode = 0;

            exec($command, $output, $exitCode);

            if ($testRunJob->getExpectedExitCode() === $exitCode) {
                unlink($path);
            }

            $testRunJob->setOutput($output);
            $testRunJob->setExitCode($exitCode);
        } else {
            $testRunJob->setExitCode(255);
        }
    }
}
