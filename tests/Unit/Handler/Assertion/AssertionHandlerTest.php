<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Assertion;

use PHPUnit\Framework\TestCase;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStatementException;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\AssertionHandler;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\ComparisonAssertionHandler;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\ExistenceAssertionHandler;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\IsRegExpAssertionHandler;
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

    public function testHandleWrapsUnsupportedContentException(): void
    {
        $assertionParser = AssertionParser::create();
        $assertion = $assertionParser->parse('$elements.examined is "value"', 0);

        $expectedUnsupportedContentException = new UnsupportedContentException(
            UnsupportedContentException::TYPE_VALUE,
            '$elements.examined'
        );

        $comparisonHandler = \Mockery::mock(ComparisonAssertionHandler::class);
        $comparisonHandler
            ->shouldReceive('handle')
            ->with($assertion)
            ->andThrow($expectedUnsupportedContentException)
        ;

        $handler = new AssertionHandler(
            $comparisonHandler,
            \Mockery::mock(ExistenceAssertionHandler::class),
            \Mockery::mock(IsRegExpAssertionHandler::class)
        );

        $this->expectExceptionObject(new UnsupportedStatementException(
            $assertion,
            $expectedUnsupportedContentException
        ));

        $handler->handle($assertion);
    }

    public function testHandleThrowsUnsupportedStatementException(): void
    {
        $assertion = new Assertion(
            '$".selector" invalid-comparison "value"',
            0,
            '$".selector"',
            'invalid-comparison',
            '"value"'
        );

        $handler = new AssertionHandler(
            \Mockery::mock(ComparisonAssertionHandler::class),
            \Mockery::mock(ExistenceAssertionHandler::class),
            \Mockery::mock(IsRegExpAssertionHandler::class)
        );

        $this->expectExceptionObject(new UnsupportedStatementException($assertion));

        $handler->handle($assertion);
    }

    public function assertRenderResolvable(string $expectedString, ResolvableInterface $resolvable): void
    {
        self::assertSame(
            $expectedString,
            ResolvableRenderer::resolve($resolvable)
        );
    }
}
