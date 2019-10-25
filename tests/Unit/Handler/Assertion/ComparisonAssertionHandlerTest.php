<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\ComparisonAssertionHandler;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\ExcludesAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\ExistsAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\IncludesAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\IsAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\IsNotAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\MatchesAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\NotExistsAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\UnhandledAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\AbstractHandlerTest;
use webignition\BasilModel\Assertion\AssertionInterface;
use webignition\BasilModel\Assertion\ExaminationAssertion;
use webignition\BasilModelFactory\AssertionFactory;

class ComparisonAssertionHandlerTest extends AbstractHandlerTest
{
    use ExcludesAssertionDataProviderTrait;
    use ExistsAssertionDataProviderTrait;
    use IncludesAssertionDataProviderTrait;
    use IsAssertionDataProviderTrait;
    use IsNotAssertionDataProviderTrait;
    use MatchesAssertionDataProviderTrait;
    use NotExistsAssertionDataProviderTrait;
    use UnhandledAssertionDataProviderTrait;

    protected function createHandler(): HandlerInterface
    {
        return ComparisonAssertionHandler::createHandler();
    }

    /**
     * @dataProvider excludesAssertionDataProvider
     * @dataProvider includesAssertionDataProvider
     * @dataProvider isAssertionDataProvider
     * @dataProvider isNotAssertionDataProvider
     * @dataProvider matchesAssertionDataProvider
     */
    public function testHandlesDoesHandle(AssertionInterface $model)
    {
        $this->assertTrue($this->handler->handles($model));
    }

    /**
     * @dataProvider existsAssertionDataProvider
     * @dataProvider notExistsAssertionDataProvider
     */
    public function testHandlesDoesNotHandle(object $model)
    {
        $this->assertFalse($this->handler->handles($model));
    }

    public function testTranspileWrongComparisonType()
    {
        $assertionFactory = AssertionFactory::createFactory();
        $model = $assertionFactory->createFromAssertionString('".selector" exists');

        $this->expectException(UnsupportedModelException::class);
        $this->expectExceptionMessage('Unsupported model "' . ExaminationAssertion::class . '"');

        $this->handler->createSource($model);
    }
}
