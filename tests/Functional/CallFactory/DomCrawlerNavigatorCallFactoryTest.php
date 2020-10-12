<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\CallFactory;

use Facebook\WebDriver\WebDriverElement;
use webignition\BasilCompilableSource\Body\Body;
use webignition\BasilCompilableSource\Body\BodyInterface;
use webignition\BasilCompilableSource\Expression\AssignmentExpression;
use webignition\BasilCompilableSource\Expression\ExpressionInterface;
use webignition\BasilCompilableSource\Expression\LiteralExpression;
use webignition\BasilCompilableSource\Statement\Statement;
use webignition\BasilCompilableSource\VariableName;
use webignition\BasilCompilableSourceFactory\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\ElementIdentifierCallFactory;
use webignition\BasilCompilableSourceFactory\ElementIdentifierSerializer;
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
        BodyInterface $teardownStatements
    ) {
        $source = $this->factory->createFindCall($elementIdentifierExpression);

        $collectionPlaceholder = new VariableName('collection');

        $instrumentedSource = new Body([
            new Statement(
                new AssignmentExpression($collectionPlaceholder, $source)
            ),
        ]);

        $classCode = $this->testCodeGenerator->createBrowserTestForBlock(
            $instrumentedSource,
            $fixture,
            null,
            $teardownStatements
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
        $elementIdentifierSerializer = ElementIdentifierSerializer::createSerializer();

        return [
            'no parent, has ordinal position' => [
                'fixture' => '/form.html',
                'elementIdentifierExpression' => $elementIdentifierCallFactory->createConstructorCall(
                    $elementIdentifierSerializer->serialize(new ElementIdentifier('input', 1))
                ),
                'teardownStatements' => new Body([
                    StatementFactory::createAssertCount('1', '$collection'),
                    new Statement(new LiteralExpression('$element = $collection->get(0)')),
                    StatementFactory::createAssertInstanceOf('\'' . WebDriverElement::class . '\'', '$element'),
                    StatementFactory::createAssertSame("'input-without-value'", '$element->getAttribute(\'name\')'),
                ]),
            ],
            'has parent' => [
                'fixture' => '/form.html',
                'elementIdentifierExpression' => $elementIdentifierCallFactory->createConstructorCall(
                    $elementIdentifierSerializer->serialize(
                        (new ElementIdentifier('input'))
                            ->withParentIdentifier(new ElementIdentifier('form[action="/action2"]'))
                    )
                ),
                'teardownStatements' => new Body([
                    StatementFactory::createAssertCount('1', '$collection'),
                    new Statement(new LiteralExpression('$element = $collection->get(0)')),
                    StatementFactory::createAssertInstanceOf('\'' . WebDriverElement::class . '\'', '$element'),
                    StatementFactory::createAssertSame("'input-2'", '$element->getAttribute(\'name\')'),
                ]),
            ],
        ];
    }
}
