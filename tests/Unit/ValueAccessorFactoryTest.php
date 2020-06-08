<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit;

use webignition\BasilCompilableSource\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\ElementIdentifierSerializer;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\DomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilableSourceFactory\Tests\Services\ObjectReflector;
use webignition\BasilCompilableSourceFactory\ValueAccessorFactory;
use webignition\BasilDomIdentifierFactory\Factory;
use webignition\BasilDomIdentifierFactory\Factory as DomIdentifierFactory;

class ValueAccessorFactoryTest extends \PHPUnit\Framework\TestCase
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
    public function testCreate(string $value, ExpressionInterface $expectedExpression)
    {
        $this->assertEquals($expectedExpression, $this->factory->create($value));
    }

    public function createDataProvider(): array
    {
        $scalarValueHandler = ScalarValueHandler::createHandler();
        $domIdentifierHandler = DomIdentifierHandler::createHandler();
        $elementIdentifierSerializer = ElementIdentifierSerializer::createSerializer();
        $domIdentifierFactory =  DomIdentifierFactory::createFactory();

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
                        $domIdentifierFactory->createFromIdentifierString('$".selector"')
                    )
                ),
            ],
            'attribute identifier' => [
                'value' => '$".selector".attribute_name',
                'expectedExpression' => $domIdentifierHandler->handleAttributeValue(
                    $elementIdentifierSerializer->serialize(
                        $domIdentifierFactory->createFromIdentifierString('$".selector"'),
                    ),
                    'attribute_name'
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
    ) {
        if (null !== $initializer) {
            $initializer($this->factory);
        }

        $this->expectExceptionObject($expectedException);

        $this->factory->create($value);
    }

    public function createThrowsExceptionDataProvider(): array
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
                        ->andReturnNull();

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
