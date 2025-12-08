<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit;

use PHPUnit\Framework\TestCase;
use webignition\BasilCompilableSourceFactory\ElementIdentifierSerializer;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\DomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilableSourceFactory\Model\Expression\ComparisonExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\ValueAccessorFactory;
use webignition\BasilDomIdentifierFactory\Factory;
use webignition\DomElementIdentifier\ElementIdentifier;
use webignition\ObjectReflector\ObjectReflector;

class ValueAccessorFactoryTest extends TestCase
{
    private ValueAccessorFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = ValueAccessorFactory::createFactory();
    }

    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(string $value, ExpressionInterface $expectedExpression): void
    {
        $this->assertEquals($expectedExpression, $this->factory->create($value));
    }

    /**
     * @return array<mixed>
     */
    public static function createDataProvider(): array
    {
        $scalarValueHandler = ScalarValueHandler::createHandler();
        $domIdentifierHandler = DomIdentifierHandler::createHandler();
        $elementIdentifierSerializer = ElementIdentifierSerializer::createSerializer();

        return [
            'scalar, literal' => [
                'value' => '"literal"',
                'expectedExpression' => $scalarValueHandler->handle('"literal"')
            ],
            'scalar, page property' => [
                'value' => '$page.title',
                'expectedExpression' => $scalarValueHandler->handle('$page.title')
            ],
            'element identifier' => [
                'value' => '$".selector"',
                'expectedExpression' => $domIdentifierHandler->handleElementValue(
                    $elementIdentifierSerializer->serialize(
                        new ElementIdentifier('.selector')
                    )
                ),
            ],
            'attribute identifier' => [
                'value' => '$".selector".attribute_name',
                'expectedExpression' => $domIdentifierHandler->handleAttributeValue(
                    $elementIdentifierSerializer->serialize(
                        new ElementIdentifier('.selector'),
                    ),
                    'attribute_name'
                ),
            ],
        ];
    }

    /**
     * @dataProvider createWithDefaultIfNullDataProvider
     */
    public function testCreateWithDefaultIfNull(string $value, ExpressionInterface $expectedExpression): void
    {
        $this->assertEquals($expectedExpression, $this->factory->createWithDefaultIfNull($value));
    }

    /**
     * @return array<mixed>
     */
    public static function createWithDefaultIfNullDataProvider(): array
    {
        $scalarValueHandler = ScalarValueHandler::createHandler();
        $domIdentifierHandler = DomIdentifierHandler::createHandler();
        $elementIdentifierSerializer = ElementIdentifierSerializer::createSerializer();

        return [
            'scalar, literal' => [
                'value' => '"literal"',
                'expectedExpression' => new ComparisonExpression(
                    $scalarValueHandler->handle('"literal"'),
                    new LiteralExpression('null'),
                    '??'
                ),
            ],
            'element identifier' => [
                'value' => '$".selector"',
                'expectedExpression' => $domIdentifierHandler->handleElementValue(
                    $elementIdentifierSerializer->serialize(
                        new ElementIdentifier('.selector')
                    )
                ),
            ],
        ];
    }

    /**
     * @dataProvider createThrowsExceptionDataProvider
     */
    public function testCreateThrowsException(
        string $value,
        \Exception $expectedException,
        ?callable $initializer = null
    ): void {
        if (null !== $initializer) {
            $initializer($this->factory);
        }

        $this->expectExceptionObject($expectedException);

        $this->factory->create($value);
    }

    /**
     * @return array<mixed>
     */
    public static function createThrowsExceptionDataProvider(): array
    {
        return [
            'value is null' => [
                'value' => '',
                'expectedException' => new UnsupportedContentException(UnsupportedContentException::TYPE_VALUE, '')
            ],
            'value identifier cannot be extracted' => [
                'value' => '$".duration"',
                'expectedException' => new UnsupportedContentException(
                    UnsupportedContentException::TYPE_IDENTIFIER,
                    '$".duration"'
                ),
                'initializer' => function (ValueAccessorFactory $factory) {
                    $domIdentifierFactory = \Mockery::mock(Factory::class);
                    $domIdentifierFactory
                        ->shouldReceive('createFromIdentifierString')
                        ->with('$".duration"')
                        ->andReturnNull()
                    ;

                    ObjectReflector::setProperty(
                        $factory,
                        ValueAccessorFactory::class,
                        'domIdentifierFactory',
                        $domIdentifierFactory
                    );
                },
            ],
        ];
    }
}
