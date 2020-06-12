<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Assertion;

use webignition\BasilCompilableSource\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\IdentifierExistenceAssertionHandler;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;
use webignition\BasilDomIdentifierFactory\Factory;
use webignition\BasilModels\Assertion\AssertionInterface;
use webignition\BasilParser\AssertionParser;
use webignition\ObjectReflector\ObjectReflector;

class IdentifierExistsAssertionHandlerTest extends \PHPUnit\Framework\TestCase
{
    use Assertion\CreateFromIdentifierExistsAssertionDataProviderTrait;
    use Assertion\CreateFromIdentifierNotExistsAssertionDataProviderTrait;

    /**
     * @dataProvider createFromIdentifierExistsAssertionDataProvider
     * @dataProvider createFromIdentifierNotExistsAssertionDataProvider
     */
    public function testHandle(
        AssertionInterface $assertion,
        string $expectedRenderedContent,
        MetadataInterface $expectedMetadata
    ) {
        $handler = IdentifierExistenceAssertionHandler::createHandler();

        $source = $handler->handle($assertion);

        $this->assertEquals($expectedRenderedContent, $source->render());
        $this->assertEquals($expectedMetadata, $source->getMetadata());
    }

    /**
     * @dataProvider handleThrowsExceptionDataProvider
     */
    public function testHandleThrowsException(
        AssertionInterface $assertion,
        \Exception $expectedException,
        ?callable $initializer = null
    ) {
        $handler = IdentifierExistenceAssertionHandler::createHandler();

        if (null !== $initializer) {
            $initializer($handler);
        }

        $this->expectExceptionObject($expectedException);

        $handler->handle($assertion);
    }

    public function handleThrowsExceptionDataProvider(): array
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
                        ->andReturnNull();

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
