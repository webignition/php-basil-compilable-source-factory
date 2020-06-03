<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\CallFactory;

use Facebook\WebDriver\WebDriverElement;
use webignition\BasilCompilableSource\Block\CodeBlock;
use webignition\BasilCompilableSource\Block\CodeBlockInterface;
use webignition\BasilCompilableSource\Line\ExpressionInterface;
use webignition\BasilCompilableSource\Line\LiteralExpression;
use webignition\BasilCompilableSource\Line\Statement\AssignmentStatement;
use webignition\BasilCompilableSource\Line\Statement\Statement;
use webignition\BasilCompilableSource\ResolvablePlaceholder;
use webignition\BasilCompilableSourceFactory\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\ElementIdentifierCallFactory;
use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractBrowserTestCase;
use webignition\BasilCompilableSourceFactory\Tests\Services\StatementFactory;
use webignition\BasilCompilableSourceFactory\Tests\Services\TestRunJob;
use webignition\DomElementIdentifier\ElementIdentifier;

class DomCrawlerNavigatorCallFactoryTest extends AbstractBrowserTestCase
{
    private DomCrawlerNavigatorCallFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = DomCrawlerNavigatorCallFactory::createFactory();
    }

    /**
     * @dataProvider createFindCallDataProvider
     */
    public function testCreateFindCall(
        string $fixture,
        ExpressionInterface $elementIdentifierExpression,
        CodeBlockInterface $teardownStatements
    ) {
        $source = $this->factory->createFindCall($elementIdentifierExpression);

        $collectionPlaceholder = ResolvablePlaceholder::createExport('COLLECTION');

        $instrumentedSource = new CodeBlock([
            new AssignmentStatement($collectionPlaceholder, $source),
        ]);

        $classCode = $this->testCodeGenerator->createBrowserTestForBlock(
            $instrumentedSource,
            $fixture,
            null,
            $teardownStatements,
            [
                $collectionPlaceholder->getName() => '$collection',
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

    public function createFindCallDataProvider(): array
    {
        $elementIdentifierCallFactory = ElementIdentifierCallFactory::createFactory();

        return [
            'no parent, has ordinal position' => [
                'fixture' => '/form.html',
                'elementIdentifierExpression' => $elementIdentifierCallFactory->createConstructorCall(
                    new ElementIdentifier('input', 1)
                ),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createAssertCount('1', '$collection'),
                    new Statement(new LiteralExpression('$element = $collection->get(0)')),
                    StatementFactory::createAssertInstanceOf('\'' . WebDriverElement::class . '\'', '$element'),
                    StatementFactory::createAssertSame("'input-without-value'", '$element->getAttribute(\'name\')'),
                ]),
            ],
            'has parent' => [
                'fixture' => '/form.html',
                'elementIdentifierExpression' => $elementIdentifierCallFactory->createConstructorCall(
                    (new ElementIdentifier('input'))
                        ->withParentIdentifier(new ElementIdentifier('form[action="/action2"]'))
                ),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createAssertCount('1', '$collection'),
                    new Statement(new LiteralExpression('$element = $collection->get(0)')),
                    StatementFactory::createAssertInstanceOf('\'' . WebDriverElement::class . '\'', '$element'),
                    StatementFactory::createAssertSame("'input-2'", '$element->getAttribute(\'name\')'),
                ]),
            ],
        ];
    }
}
