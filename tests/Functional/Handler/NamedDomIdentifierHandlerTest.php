<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\Handler;

use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifier;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifierInterface;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifierValue;
use webignition\BasilCompilableSourceFactory\Handler\NamedDomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Tests\Services\StatementFactory;
use webignition\BasilCompilableSourceFactory\Tests\Services\TestRunJob;
use webignition\BasilCompilationSource\LineList;
use webignition\BasilCompilationSource\Statement;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Value\DomIdentifierValue;

class NamedDomIdentifierHandlerTest extends AbstractHandlerTest
{
    protected function createHandler(): HandlerInterface
    {
        return NamedDomIdentifierHandler::createHandler();
    }
    /**
     * @dataProvider createSourceDataProvider
     */
    public function testCreateSource(
        string $fixture,
        NamedDomIdentifierInterface $namedDomIdentifier,
        LineList $teardownStatements
    ) {
        $source = $this->handler->createSource($namedDomIdentifier);

        $classCode = $this->testCodeGenerator->createBrowserTestForLineList(
            $source,
            $fixture,
            null,
            $teardownStatements,
            [
                'HAS' => '$has',
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

    public function createSourceDataProvider(): array
    {
        return [
            'element value, no parent' => [
                'fixture' => '/form.html',
                'model' => new NamedDomIdentifierValue(
                    new DomIdentifierValue(
                        new DomIdentifier('input', 1)
                    ),
                    new VariablePlaceholder('ELEMENT')
                ),
                'teardownStatements' => new LineList([
                    StatementFactory::createAssertSame('""', '$value'),
                ]),
            ],
            'element value, has parent' => [
                'fixture' => '/form.html',
                'model' => new NamedDomIdentifierValue(
                    new DomIdentifierValue(
                        (new DomIdentifier('input', 1))
                            ->withParentIdentifier(new DomIdentifier('form[action="/action2"]'))
                    ),
                    new VariablePlaceholder('ELEMENT')
                ),
                'teardownStatements' => new LineList([
                    StatementFactory::createAssertSame('"test"', '$value'),
                ]),
            ],
            'attribute value, no parent' => [
                'fixture' => '/form.html',
                'model' => new NamedDomIdentifierValue(
                    new DomIdentifierValue(
                        (new DomIdentifier('input', 1))->withAttributeName('name')
                    ),
                    new VariablePlaceholder('ELEMENT')
                ),
                'teardownStatements' => new LineList([
                    StatementFactory::createAssertSame('"input-without-value"', '$value'),
                ]),
            ],
            'attribute value, has parent' => [
                'fixture' => '/form.html',
                'model' => new NamedDomIdentifierValue(
                    new DomIdentifierValue(
                        (new DomIdentifier('input', 1))
                            ->withAttributeName('name')
                            ->withParentIdentifier(new DomIdentifier('form[action="/action2"]'))
                    ),
                    new VariablePlaceholder('ELEMENT')
                ),
                'teardownStatements' => new LineList([
                    StatementFactory::createAssertSame('"input-2"', '$value'),
                ]),
            ],
            'element identifier, no parent' => [
                'fixture' => '/form.html',
                'model' => new NamedDomIdentifier(
                    new DomIdentifier('input', 1),
                    new VariablePlaceholder('ELEMENT')
                ),
                'teardownStatements' => new LineList([
                    StatementFactory::createAssertCount('1', '$value'),
                    new Statement('$element = $value->current()'),
                    StatementFactory::createAssertSame('""', '$element->getAttribute(\'value\')'),
                ]),
            ],
            'element identifier, has parent' => [
                'fixture' => '/form.html',
                'model' => new NamedDomIdentifier(
                    (new DomIdentifier('input', 1))
                        ->withParentIdentifier(new DomIdentifier('form[action="/action2"]')),
                    new VariablePlaceholder('ELEMENT')
                ),
                'teardownStatements' => new LineList([
                    StatementFactory::createAssertCount('1', '$value'),
                    new Statement('$element = $value->current()'),
                    StatementFactory::createAssertSame('null', '$element->getAttribute(\'test\')'),
                ]),
            ],
        ];
    }
}
