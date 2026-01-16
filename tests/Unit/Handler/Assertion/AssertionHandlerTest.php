<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Assertion;

use PHPUnit\Framework\TestCase;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStatementException;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\AssertionHandler;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion as AssertionDataProvider;
use webignition\BasilCompilableSourceFactory\Tests\Services\ResolvableRenderer;
use webignition\BasilModels\Model\Assertion\Assertion;
use webignition\BasilModels\Model\Assertion\AssertionInterface;
use webignition\BasilModels\Parser\AssertionParser;
use webignition\Stubble\Resolvable\ResolvableInterface;

class AssertionHandlerTest extends TestCase
{
    use AssertionDataProvider\CreateFromExcludesAssertionDataProviderTrait;
    use AssertionDataProvider\CreateFromIncludesAssertionDataProviderTrait;
    use AssertionDataProvider\CreateFromIsAssertionDataProviderTrait;
    use AssertionDataProvider\CreateFromIsNotAssertionDataProviderTrait;
    use AssertionDataProvider\CreateFromMatchesAssertionDataProviderTrait;
    use AssertionDataProvider\CreateFromIdentifierExistsAssertionDataProviderTrait;
    use AssertionDataProvider\CreateFromIdentifierNotExistsAssertionDataProviderTrait;
    use AssertionDataProvider\CreateFromIsRegExpAssertionDataProviderTrait;
    use AssertionDataProvider\CreateFromScalarExistsAssertionDataProviderTrait;

    /**
     * @dataProvider createFromExcludesAssertionDataProvider
     * @dataProvider createFromIncludesAssertionDataProvider
     * @dataProvider createFromIsAssertionDataProvider
     * @dataProvider createFromIsNotAssertionDataProvider
     * @dataProvider createFromMatchesAssertionDataProvider
     * @dataProvider createFromIdentifierExistsAssertionDataProvider
     * @dataProvider createFromIdentifierNotExistsAssertionDataProvider
     * @dataProvider createFromIsRegExpAssertionDataProvider
     * @dataProvider createFromScalarExistsAssertionDataProvider
     */
    public function testHandleSuccess(
        AssertionInterface $assertion,
        string $expectedRenderedContent,
        MetadataInterface $expectedMetadata
    ): void {
        $handler = AssertionHandler::createHandler();

        $source = $handler->handle($assertion);

        $this->assertRenderResolvable($expectedRenderedContent, $source);
        $this->assertEquals($expectedMetadata, $source->getMetadata());
    }

    /**
     * @dataProvider handleThrowsUnsupportedStatementExceptionDataProvider
     */
    public function testHandleThrowsUnsupportedStatementException(
        AssertionInterface $assertion,
        UnsupportedStatementException $expected
    ): void {
        $handler = AssertionHandler::createHandler();

        try {
            $handler->handle($assertion);
        } catch (UnsupportedStatementException $exception) {
            self::assertSame((string) $expected->getStatement(), (string) $exception->getStatement());
            self::assertSame($expected->getCode(), $exception->getCode());
        }
    }

    /**
     * @return array<mixed>
     */
    public static function handleThrowsUnsupportedStatementExceptionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        $invalidExistsAssertion = new Assertion(
            'invalid exists',
            0,
            'invalid',
            'exists',
        );

        return [
            'comparison assertion, unsupported comparison' => [
                'assertion' => $assertionParser->parse('$".selector" foo "value"', 0),
                'expected' => new UnsupportedStatementException(
                    $assertionParser->parse('$".selector" foo "value"', 0)
                ),
            ],
            'comparison assertion, examined value is not supported' => [
                'assertion' => $assertionParser->parse('$elements.examined is "value"', 0),
                'expected' => new UnsupportedStatementException(
                    $assertionParser->parse('$elements.examined is "value"', 0),
                    new UnsupportedContentException(
                        UnsupportedContentException::TYPE_VALUE,
                        '$elements.examined'
                    )
                ),
            ],
            'comparison assertion, expected value is not supported' => [
                'assertion' => $assertionParser->parse('$".selector" is $elements.expected', 0),
                'expected' => new UnsupportedStatementException(
                    $assertionParser->parse('$".selector" is $elements.expected', 0),
                    new UnsupportedContentException(
                        UnsupportedContentException::TYPE_VALUE,
                        '$elements.expected'
                    )
                ),
            ],
            'existence assertion, unsupported identifier' => [
                'assertion' => $invalidExistsAssertion,
                'expected' => new UnsupportedStatementException(
                    $invalidExistsAssertion,
                    new UnsupportedContentException(
                        UnsupportedContentException::TYPE_IDENTIFIER,
                        'invalid'
                    )
                ),
            ],
            'existence assertion, identifier is not supported' => [
                'assertion' => $assertionParser->parse('$elements.element_name exists', 0),
                'expected' => new UnsupportedStatementException(
                    $assertionParser->parse('$elements.element_name exists', 0),
                    new UnsupportedContentException(
                        UnsupportedContentException::TYPE_IDENTIFIER,
                        '$elements.element_name'
                    )
                ),
            ],
        ];
    }

    private function assertRenderResolvable(string $expectedString, ResolvableInterface $resolvable): void
    {
        self::assertSame(
            $expectedString,
            ResolvableRenderer::resolve($resolvable)
        );
    }
}
