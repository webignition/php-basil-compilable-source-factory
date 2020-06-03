<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\Handler;

use webignition\BasilCompilableSource\Block\CodeBlock;
use webignition\BasilCompilableSource\Block\CodeBlockInterface;
use webignition\BasilCompilableSource\Line\LiteralExpression;
use webignition\BasilCompilableSource\Line\Statement\AssignmentStatement;
use webignition\BasilCompilableSource\Line\Statement\Statement;
use webignition\BasilCompilableSource\ResolvablePlaceholder;
use webignition\BasilCompilableSourceFactory\Handler\DomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Model\DomIdentifier;
use webignition\BasilCompilableSourceFactory\Model\DomIdentifierInterface;
use webignition\BasilCompilableSourceFactory\Model\DomIdentifierValue;
use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractBrowserTestCase;
use webignition\BasilCompilableSourceFactory\Tests\Services\StatementFactory;
use webignition\BasilCompilableSourceFactory\Tests\Services\TestRunJob;
use webignition\DomElementIdentifier\AttributeIdentifier;
use webignition\DomElementIdentifier\ElementIdentifier;

class DomIdentifierHandlerTest extends AbstractBrowserTestCase
{
    private DomIdentifierHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = DomIdentifierHandler::createHandler();
    }

    /**
     * @dataProvider handleDataProvider
     */
    public function testHandle(
        string $fixture,
        DomIdentifierInterface $domIdentifier,
        CodeBlockInterface $teardownStatements
    ) {
        $source = $this->handler->handle($domIdentifier);

        $instrumentedSource = new AssignmentStatement(
            ResolvablePlaceholder::createExport('ELEMENT'),
            $source
        );

        $classCode = $this->testCodeGenerator->createBrowserTestForBlock(
            new CodeBlock([
                $instrumentedSource,
            ]),
            $fixture,
            null,
            $teardownStatements,
            [
                'ELEMENT' => '$value',
            ]
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

    public function handleDataProvider(): array
    {
        return [
            'element value, no parent' => [
                'fixture' => '/form.html',
                'model' => new DomIdentifierValue(
                    new ElementIdentifier('input', 1)
                ),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createAssertSame('""', '$value'),
                ]),
            ],
            'element value, has parent' => [
                'fixture' => '/form.html',
                'model' => new DomIdentifierValue(
                    (new ElementIdentifier('input', 1))
                        ->withParentIdentifier(new ElementIdentifier('form[action="/action2"]'))
                ),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createAssertSame('"test"', '$value'),
                ]),
            ],
            'attribute value, no parent' => [
                'fixture' => '/form.html',
                'model' => new DomIdentifierValue(
                    new AttributeIdentifier('input', 'name', 1)
                ),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createAssertSame('"input-without-value"', '$value'),
                ]),
            ],
            'attribute value, has parent' => [
                'fixture' => '/form.html',
                'model' => new DomIdentifierValue(
                    (new AttributeIdentifier('input', 'name', 1))
                        ->withParentIdentifier(new ElementIdentifier('form[action="/action2"]'))
                ),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createAssertSame('"input-2"', '$value'),
                ]),
            ],
            'element identifier, no parent' => [
                'fixture' => '/form.html',
                'model' => new DomIdentifier(
                    new ElementIdentifier('input', 1)
                ),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createAssertCount('1', '$value'),
                    new Statement(new LiteralExpression('$element = $value->current()')),
                    StatementFactory::createAssertSame('""', '$element->getAttribute(\'value\')'),
                ]),
            ],
            'element identifier, has parent' => [
                'fixture' => '/form.html',
                'model' => new DomIdentifier(
                    (new ElementIdentifier('input', 1))
                        ->withParentIdentifier(new ElementIdentifier('form[action="/action2"]'))
                ),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createAssertCount('1', '$value'),
                    new Statement(new LiteralExpression('$element = $value->current()')),
                    StatementFactory::createAssertSame('null', '$element->getAttribute(\'test\')'),
                ]),
            ],
        ];
    }
}
