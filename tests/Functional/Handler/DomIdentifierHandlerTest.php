<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\Handler;

use webignition\BasilCompilableSource\Body\Body;
use webignition\BasilCompilableSource\Body\BodyInterface;
use webignition\BasilCompilableSource\Expression\AssignmentExpression;
use webignition\BasilCompilableSource\Expression\ExpressionInterface;
use webignition\BasilCompilableSource\Expression\LiteralExpression;
use webignition\BasilCompilableSource\Statement\Statement;
use webignition\BasilCompilableSource\VariableName;
use webignition\BasilCompilableSourceFactory\ElementIdentifierSerializer;
use webignition\BasilCompilableSourceFactory\Handler\DomIdentifierHandler;
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
     * @dataProvider handleElementValueDataProvider
     */
    public function testHandleElementValue(
        string $fixture,
        string $serializedElementIdentifier,
        BodyInterface $teardownStatements
    ) {
        $source = $this->handler->handleElementValue($serializedElementIdentifier);

        $this->assertSource($source, $fixture, $teardownStatements);
    }

    public function handleElementValueDataProvider(): array
    {
        $elementIdentifierSerializer = ElementIdentifierSerializer::createSerializer();

        return [
            'element value, no parent' => [
                'fixture' => '/form.html',
                'serializedElementIdentifier' => $elementIdentifierSerializer->serialize(
                    new ElementIdentifier('input', 1)
                ),
                'teardownStatements' => new Body([
                    StatementFactory::createAssertSame('""', '$value'),
                ]),
            ],
            'element value, has parent' => [
                'fixture' => '/form.html',
                'serializedElementIdentifier' => $elementIdentifierSerializer->serialize(
                    (new ElementIdentifier('input', 1))
                        ->withParentIdentifier(new ElementIdentifier('form[action="/action2"]'))
                ),
                'teardownStatements' => new Body([
                    StatementFactory::createAssertSame('"test"', '$value'),
                ]),
            ],
        ];
    }

    /**
     * @dataProvider handleAttributeValueDataProvider
     */
    public function testHandleAttributeValue(
        string $fixture,
        string $serializedElementIdentifier,
        string $attributeName,
        BodyInterface $teardownStatements
    ) {
        $source = $this->handler->handleAttributeValue($serializedElementIdentifier, $attributeName);

        $this->assertSource($source, $fixture, $teardownStatements);
    }

    public function handleAttributeValueDataProvider(): array
    {
        $elementIdentifierSerializer = ElementIdentifierSerializer::createSerializer();

        return [
            'attribute value, no parent' => [
                'fixture' => '/form.html',
                'serializedElementIdentifier' => $elementIdentifierSerializer->serialize(
                    new AttributeIdentifier('input', 'name', 1)
                ),
                'attributeName' => 'name',
                'teardownStatements' => new Body([
                    StatementFactory::createAssertSame('"input-without-value"', '$value'),
                ]),
            ],
            'attribute value, has parent' => [
                'fixture' => '/form.html',
                'serializedElementIdentifier' => $elementIdentifierSerializer->serialize(
                    (new AttributeIdentifier('input', 'name', 1))
                        ->withParentIdentifier(new ElementIdentifier('form[action="/action2"]'))
                ),
                'attributeName' => 'name',
                'teardownStatements' => new Body([
                    StatementFactory::createAssertSame('"input-2"', '$value'),
                ]),
            ],
        ];
    }

    /**
     * @dataProvider handleElementCollectionDataProvider
     */
    public function testHandleElementCollection(
        string $fixture,
        string $serializedElementIdentifier,
        BodyInterface $teardownStatements
    ) {
        $source = $this->handler->handleElementCollection($serializedElementIdentifier);

        $this->assertSource($source, $fixture, $teardownStatements);
    }

    public function handleElementCollectionDataProvider(): array
    {
        $elementIdentifierSerializer = ElementIdentifierSerializer::createSerializer();

        return [
            'element, no parent' => [
                'fixture' => '/form.html',
                'serializedElementIdentifier' => $elementIdentifierSerializer->serialize(
                    new ElementIdentifier('input', 1)
                ),
                'teardownStatements' => new Body([
                    StatementFactory::createAssertCount('1', '$value'),
                    new Statement(new LiteralExpression('$element = $value->current()')),
                    StatementFactory::createAssertSame('""', '$element->getAttribute(\'value\')'),
                ]),
            ],
            'element, has parent' => [
                'fixture' => '/form.html',
                'serializedElementIdentifier' => $elementIdentifierSerializer->serialize(
                    (new ElementIdentifier('input', 1))
                        ->withParentIdentifier(new ElementIdentifier('form[action="/action2"]'))
                ),
                'teardownStatements' => new Body([
                    StatementFactory::createAssertCount('1', '$value'),
                    new Statement(new LiteralExpression('$element = $value->current()')),
                    StatementFactory::createAssertSame('null', '$element->getAttribute(\'test\')'),
                ]),
            ],
        ];
    }

    private function assertSource(
        ExpressionInterface $source,
        string $fixture,
        BodyInterface $teardownStatements
    ): void {
        $instrumentedSource = new Statement(
            new AssignmentExpression(new VariableName('value'), $source)
        );

        $classCode = $this->testCodeGenerator->createBrowserTestForBlock(
            new Body([
                $instrumentedSource,
            ]),
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
}
