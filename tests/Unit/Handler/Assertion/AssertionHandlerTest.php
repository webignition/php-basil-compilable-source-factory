<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Assertion;

use webignition\BasilCompilableSource\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStatementException;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\ExistenceAssertionHandler;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\AssertionHandler;
use webignition\BasilCompilableSourceFactory\Tests\Services\ObjectReflector;
use webignition\BasilModels\Assertion\AssertionInterface;
use webignition\BasilParser\AssertionParser;

class AssertionHandlerTest extends \PHPUnit\Framework\TestCase
{
    use Assertion\CreateFromIdentifierExistsAssertionDataProviderTrait;
    use Assertion\CreateFromScalarExistsAssertionDataProviderTrait;
    use Assertion\CreateFromIsRegExpAssertionDataProviderTrait;
    use Assertion\CreateFromIdentifierNotExistsAssertionDataProviderTrait;

    /**
     * @dataProvider createFromIdentifierExistsAssertionDataProvider
     * @dataProvider createFromScalarExistsAssertionDataProvider
     * @dataProvider createFromIdentifierNotExistsAssertionDataProvider
     * @dataProvider createFromIsRegExpAssertionDataProvider
     */
    public function testHandle(
        AssertionInterface $assertion,
        string $expectedRenderedContent,
        MetadataInterface $expectedMetadata
    ) {
        $handler = AssertionHandler::createHandler();

        $source = $handler->handle($assertion);

        $this->assertEquals($expectedRenderedContent, $source->render());
        $this->assertEquals($expectedMetadata, $source->getMetadata());
    }

    /**
     * @dataProvider handleThrowsExceptionDataProvider
     */
    public function testHandleThrowsException(
        AssertionInterface $assertion,
        UnsupportedStatementException $expectedException,
        ?callable $initializer = null
    ) {
        $handler = AssertionHandler::createHandler();

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
            'unsupported comparison' => [
                'assertion' => $assertionParser->parse('$".selector" foo "value"'),
                'expectedException' => new UnsupportedStatementException(
                    $assertionParser->parse('$".selector" foo "value"')
                ),
            ],
            'existence; identifier is not supported' => [
                'assertion' => $assertionParser->parse('$elements.element_name exists'),
                'expectedException' => new UnsupportedStatementException(
                    $assertionParser->parse('$elements.element_name exists'),
                    new UnsupportedContentException(
                        UnsupportedContentException::TYPE_IDENTIFIER,
                        '$elements.element_name'
                    )
                ),
            ],
            'existence; identifier cannot be extracted' => [
                'assertion' => $assertionParser->parse('$".selector" exists'),
                'expectedException' => new  UnsupportedStatementException(
                    $assertionParser->parse('$".selector" exists'),
                    new UnsupportedContentException(
                        UnsupportedContentException::TYPE_IDENTIFIER,
                        '$".selector"'
                    )
                ),
                'initializer' => function (AssertionHandler $handler) {
                    $existenceAssertionHandler = \Mockery::mock(ExistenceAssertionHandler::class);
                    $existenceAssertionHandler
                        ->shouldReceive('handle')
                        ->andThrow(new UnsupportedContentException(
                            UnsupportedContentException::TYPE_IDENTIFIER,
                            '$".selector"'
                        ));

                    ObjectReflector::setProperty(
                        $handler,
                        AssertionHandler::class,
                        'existenceAssertionHandler',
                        $existenceAssertionHandler
                    );
                },
            ],
            'comparison; examined value is not supported' => [
                'assertion' => $assertionParser->parse('$elements.examined is "value"'),
                'expectedException' => new UnsupportedStatementException(
                    $assertionParser->parse('$elements.examined is "value"'),
                    new UnsupportedContentException(
                        UnsupportedContentException::TYPE_VALUE,
                        '$elements.examined'
                    )
                ),
            ],
            'comparison; expected value is not supported' => [
                'assertion' => $assertionParser->parse('$".selector" is $elements.expected'),
                'expectedException' => new UnsupportedStatementException(
                    $assertionParser->parse('$".selector" is $elements.expected'),
                    new UnsupportedContentException(
                        UnsupportedContentException::TYPE_VALUE,
                        '$elements.expected'
                    )
                ),
            ],
        ];
    }
}
