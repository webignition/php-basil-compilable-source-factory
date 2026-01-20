<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\CallFactory;

use Facebook\WebDriver\WebDriverElement;
use PHPUnit\Framework\Attributes\DataProvider;
use SmartAssert\DomIdentifier\ElementIdentifier;
use webignition\BasilCompilableSourceFactory\ArgumentFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilCompilableSourceFactory\ElementIdentifierSerializer;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\VariableName;
use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractBrowserTestCase;
use webignition\BasilCompilableSourceFactory\Tests\Services\StatementFactory;
use webignition\BasilCompilableSourceFactory\Tests\Services\TestRunJob;

class DomCrawlerNavigatorCallFactoryTest extends AbstractBrowserTestCase
{
    private DomCrawlerNavigatorCallFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = DomCrawlerNavigatorCallFactory::createFactory();
    }

    #[DataProvider('createFindCallDataProvider')]
    public function testCreateFindCall(
        string $fixture,
        ExpressionInterface $expression,
        BodyInterface $teardownStatements
    ): void {
        $source = $this->factory->createFindCall($expression);

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

    /**
     * @return array<mixed>
     */
    public static function createFindCallDataProvider(): array
    {
        $elementIdentifierSerializer = ElementIdentifierSerializer::createSerializer();

        $argumentFactory = ArgumentFactory::createFactory();

        return [
            'no parent, has ordinal position' => [
                'fixture' => '/form.html',
                'expression' => $argumentFactory->createSingular(
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
                'expression' => $argumentFactory->createSingular(
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
