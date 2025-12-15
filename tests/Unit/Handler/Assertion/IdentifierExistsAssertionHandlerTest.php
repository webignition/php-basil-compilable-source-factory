<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\IdentifierExistenceAssertionHandler;
use webignition\BasilCompilableSourceFactory\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractResolvableTestCase;
use webignition\BasilDomIdentifierFactory\Factory;
use webignition\BasilModels\Model\Assertion\AssertionInterface;
use webignition\BasilModels\Parser\AssertionParser;
use webignition\ObjectReflector\ObjectReflector;

class IdentifierExistsAssertionHandlerTest extends AbstractResolvableTestCase
{
    use Assertion\CreateFromIdentifierExistsAssertionDataProviderTrait;
    use Assertion\CreateFromIdentifierNotExistsAssertionDataProviderTrait;

    /**
     * @dataProvider createFromIdentifierExistsAssertionDataProvider
     * @dataProvider createFromIdentifierNotExistsAssertionDataProvider
     */
    public function testHandle(
        AssertionInterface $assertion,
        Metadata $metadata,
        string $expectedRenderedContent,
        MetadataInterface $expectedMetadata
    ): void {
        $handler = IdentifierExistenceAssertionHandler::createHandler();

        $source = $handler->handle($assertion, $metadata);

        $this->assertRenderResolvable($expectedRenderedContent, $source);
        $this->assertEquals($expectedMetadata, $source->getMetadata());
    }

    /**
     * @dataProvider handleThrowsExceptionDataProvider
     */
    public function testHandleThrowsException(
        AssertionInterface $assertion,
        \Exception $expectedException,
        ?callable $initializer = null
    ): void {
        $handler = IdentifierExistenceAssertionHandler::createHandler();

        if (null !== $initializer) {
            $initializer($handler);
        }

        $this->expectExceptionObject($expectedException);

        $metadata = new Metadata($assertion);

        $handler->handle($assertion, $metadata);
    }

    /**
     * @return array<mixed>
     */
    public static function handleThrowsExceptionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'existence; identifier is not supported' => [
                'assertion' => $assertionParser->parse('$elements.element_name exists'),
                'expectedException' => new UnsupportedContentException(
                    UnsupportedContentException::TYPE_IDENTIFIER,
                    '$elements.element_name'
                ),
            ],
            'existence; identifier cannot be extracted' => [
                'assertion' => $assertionParser->parse('$".selector" exists'),
                'expectedException' => new UnsupportedContentException(
                    UnsupportedContentException::TYPE_IDENTIFIER,
                    '$".selector"'
                ),
                'initializer' => function (IdentifierExistenceAssertionHandler $handler) {
                    $domIdentifierFactory = \Mockery::mock(Factory::class);
                    $domIdentifierFactory
                        ->shouldReceive('createFromIdentifierString')
                        ->with('$".selector"')
                        ->andReturnNull()
                    ;

                    ObjectReflector::setProperty(
                        $handler,
                        IdentifierExistenceAssertionHandler::class,
                        'domIdentifierFactory',
                        $domIdentifierFactory
                    );
                },
            ],
        ];
    }
}
