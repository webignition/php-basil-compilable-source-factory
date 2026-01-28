<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\Handler;

use PHPUnit\Framework\Attributes\DataProvider;
use SmartAssert\DomIdentifier\AttributeIdentifier;
use SmartAssert\DomIdentifier\ElementIdentifier;
use webignition\BasilCompilableSourceFactory\ElementIdentifierSerializer;
use webignition\BasilCompilableSourceFactory\Enum\Type;
use webignition\BasilCompilableSourceFactory\Handler\DomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractBrowserTestCase;
use webignition\BasilCompilableSourceFactory\Tests\Services\StatementFactory;
use webignition\BasilCompilableSourceFactory\Tests\Services\TestRunJob;

class DomIdentifierHandlerTest extends AbstractBrowserTestCase
{
    private DomIdentifierHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = DomIdentifierHandler::createHandler();
    }

    #[DataProvider('handleElementValueDataProvider')]
    public function testHandleElementValue(
        string $fixture,
        string $serializedElementIdentifier,
        BodyInterface $teardownStatements
    ): void {
        $source = $this->handler->handleElementValue($serializedElementIdentifier);

        $this->assertSource($source, $fixture, $teardownStatements);
    }

    /**
     * @return array<mixed>
     */
    public static function handleElementValueDataProvider(): array
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

    #[DataProvider('handleAttributeValueDataProvider')]
    public function testHandleAttributeValue(
        string $fixture,
        string $serializedElementIdentifier,
        string $attributeName,
        BodyInterface $teardownStatements
    ): void {
        $source = $this->handler->handleAttributeValue($serializedElementIdentifier, $attributeName);

        $this->assertSource($source, $fixture, $teardownStatements);
    }

    /**
     * @return array<mixed>
     */
    public static function handleAttributeValueDataProvider(): array
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

    #[DataProvider('handleElementCollectionDataProvider')]
    public function testHandleElementCollection(
        string $fixture,
        string $serializedElementIdentifier,
        BodyInterface $teardownStatements
    ): void {
        $source = $this->handler->handleElementCollection($serializedElementIdentifier);

        $this->assertSource($source, $fixture, $teardownStatements);
    }

    /**
     * @return array<mixed>
     */
    public static function handleElementCollectionDataProvider(): array
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
                    new Statement(LiteralExpression::string('$element = $value->current()')),
                    StatementFactory::createAssertSame('""', '$element->getAttribute(\'value\')'),
                ]),
            ],
            'element, has parent' => [
                'fixture' => '/form.html',
                'serializedElementIdentifier' => $elementIdentifierSerializer->serialize(
                    new ElementIdentifier('input', 1)
                        ->withParentIdentifier(new ElementIdentifier('form[action="/action2"]'))
                ),
                'teardownStatements' => new Body([
                    StatementFactory::createAssertCount('1', '$value'),
                    new Statement(LiteralExpression::string('$element = $value->current()')),
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
            new AssignmentExpression(Property::asVariable('value', Type::STRING), $source)
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
