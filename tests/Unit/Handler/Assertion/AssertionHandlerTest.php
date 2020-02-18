<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Assertion;

use webignition\BasilCompilableSource\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStatementException;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\CreateFromExcludesAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\CreateFromExistsAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\CreateFromIncludesAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\CreateFromIsAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\CreateFromIsNotAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\CreateFromMatchesAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\CreateFromNotExistsAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\AssertionHandler;
use webignition\BasilModels\Assertion\AssertionInterface;
use webignition\BasilParser\AssertionParser;

class AssertionHandlerTest extends AbstractTestCase
{
    use CreateFromExcludesAssertionDataProviderTrait;
    use CreateFromExistsAssertionDataProviderTrait;
    use CreateFromIncludesAssertionDataProviderTrait;
    use CreateFromIsAssertionDataProviderTrait;
    use CreateFromIsNotAssertionDataProviderTrait;
    use CreateFromMatchesAssertionDataProviderTrait;
    use CreateFromNotExistsAssertionDataProviderTrait;

    /**
     * @var AssertionHandler
     */
    private $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = AssertionHandler::createHandler();
    }

    /**
     * @dataProvider createFromExcludesAssertionDataProvider
     * @dataProvider createFromExistsAssertionDataProvider
     * @dataProvider createFromIncludesAssertionDataProvider
     * @dataProvider createFromIsAssertionDataProvider
     * @dataProvider createFromIsNotAssertionDataProvider
     * @dataProvider createFromMatchesAssertionDataProvider
     * @dataProvider createFromNotExistsAssertionDataProvider
     */
    public function testHandle(
        AssertionInterface $assertion,
        string $expectedRenderedContent,
        MetadataInterface $expectedMetadata
    ) {
        $source = $this->handler->handle($assertion);

        $this->assertEquals($expectedRenderedContent, $source->render());
        $this->assertEquals($expectedMetadata, $source->getMetadata());
    }

    /**
     * @dataProvider handleThrowsExceptionDataProvider
     */
    public function testHandleThrowsException(
        AssertionInterface $assertion,
        UnsupportedStatementException $expectedException
    ) {
        $handler = AssertionHandler::createHandler();
        $this->expectExceptionObject($expectedException);

        $handler->handle($assertion);
    }

    public function handleThrowsExceptionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'comparison assertion, examined value is not supported' => [
                'assertion' => $assertionParser->parse('$elements.examined is "value"'),
                'expectedException' => new UnsupportedStatementException(
                    $assertionParser->parse('$elements.examined is "value"'),
                    new UnsupportedContentException(
                        UnsupportedContentException::TYPE_VALUE,
                        '$elements.examined'
                    )
                ),
            ],
            'unsupported comparison' => [
                'assertion' => $assertionParser->parse('$".selector" foo "value"'),
                'expectedException' => new UnsupportedStatementException(
                    $assertionParser->parse('$".selector" foo "value"')
                ),
            ],
        ];
    }
}
