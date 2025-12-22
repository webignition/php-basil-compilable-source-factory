<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStatementException;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\ComparisonAssertionHandler;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\CreateFromExcludesAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\CreateFromIncludesAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\CreateFromIsAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\CreateFromIsNotAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\CreateFromMatchesAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractResolvableTestCase;
use webignition\BasilModels\Model\Assertion\AssertionInterface;
use webignition\BasilModels\Parser\AssertionParser;

class ComparisonAssertionHandlerTest extends AbstractResolvableTestCase
{
    use CreateFromExcludesAssertionDataProviderTrait;
    use CreateFromIncludesAssertionDataProviderTrait;
    use CreateFromIsAssertionDataProviderTrait;
    use CreateFromIsNotAssertionDataProviderTrait;
    use CreateFromMatchesAssertionDataProviderTrait;

    /**
     * @dataProvider createFromExcludesAssertionDataProvider
     * @dataProvider createFromIncludesAssertionDataProvider
     * @dataProvider createFromIsAssertionDataProvider
     * @dataProvider createFromIsNotAssertionDataProvider
     * @dataProvider createFromMatchesAssertionDataProvider
     */
    public function testHandle(
        AssertionInterface $assertion,
        string $expectedRenderedContent,
        MetadataInterface $expectedMetadata
    ): void {
        $handler = ComparisonAssertionHandler::createHandler();

        $source = $handler->handle($assertion);

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
        $handler = ComparisonAssertionHandler::createHandler();

        if (null !== $initializer) {
            $initializer($handler);
        }

        $this->expectExceptionObject($expectedException);

        $handler->handle($assertion);
    }

    /**
     * @return array<mixed>
     */
    public static function handleThrowsExceptionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'unsupported comparison' => [
                'assertion' => $assertionParser->parse('$".selector" foo "value"'),
                'expectedException' => new UnsupportedStatementException(
                    $assertionParser->parse('$".selector" foo "value"')
                ),
            ],
            'comparison; examined value is not supported' => [
                'assertion' => $assertionParser->parse('$elements.examined is "value"'),
                'expectedException' => new UnsupportedContentException(
                    UnsupportedContentException::TYPE_VALUE,
                    '$elements.examined'
                ),
            ],
            'comparison; expected value is not supported' => [
                'assertion' => $assertionParser->parse('$".selector" is $elements.expected'),
                'expectedException' => new UnsupportedContentException(
                    UnsupportedContentException::TYPE_VALUE,
                    '$elements.expected'
                ),
            ],
        ];
    }
}
