<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\Handler\Value;

use webignition\BasilCompilableSource\Block\CodeBlock;
use webignition\BasilCompilableSource\Block\CodeBlockInterface;
use webignition\BasilCompilableSource\Line\Statement\AssignmentStatement;
use webignition\BasilCompilableSource\VariableName;
use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractBrowserTestCase;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilableSourceFactory\Tests\Services\StatementFactory;
use webignition\BasilCompilableSourceFactory\Tests\Services\TestRunJob;

class ScalarValueHandlerTest extends AbstractBrowserTestCase
{
    private ScalarValueHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = ScalarValueHandler::createHandler();
    }

    /**
     * @dataProvider createSourceDataProvider
     */
    public function testCreateSource(
        string $fixture,
        string $value,
        CodeBlockInterface $teardownStatements,
        array $additionalVariableIdentifiers = []
    ) {
        $source = $this->handler->handle($value);

        $valuePlaceholder = new VariableName('value');

        $instrumentedSource = new CodeBlock([
            new AssignmentStatement($valuePlaceholder, $source),
        ]);

        $classCode = $this->testCodeGenerator->createBrowserTestForBlock(
            $instrumentedSource,
            $fixture,
            null,
            $teardownStatements,
            $additionalVariableIdentifiers
        );

        $testRunJob = $this->testRunner->createTestRunJob($classCode);

        if ($testRunJob instanceof TestRunJob) {
            $this->testRunner->run($testRunJob);

            $this->assertSame(
                $testRunJob->getExpectedExitCode(),
                $testRunJob->getExitCode(),
                $testRunJob->getOutputAsString()
            );
        }
    }

    public function createSourceDataProvider(): array
    {
        return [
            'browser property: size' => [
                'fixture' => '/empty.html',
                'value' => '$browser.size',
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createAssertSame('"1200x1100"', '$value'),
                ]),
            ],
            'page property: title' => [
                'fixture' => '/index.html',
                'value' => '$page.title',
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createAssertBrowserTitle('Test fixture web server default document'),
                ]),
            ],
            'page property: url' => [
                'fixture' => '/index.html',
                'value' => '$page.url',
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createAssertSame('"http://127.0.0.1:9080/index.html"', '$value'),
                ]),
            ],
        ];
    }
}
