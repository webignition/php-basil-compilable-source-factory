<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Transpiler\Assertion;

use webignition\BasilCompilableSourceFactory\Exception\NonTranspilableModelException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\ExcludesAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\ExistsAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\IncludesAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\IsAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\IsNotAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\MatchesAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\NotExistsAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\UnhandledAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Transpiler\AbstractTranspilerTest;
use webignition\BasilCompilableSourceFactory\Transpiler\Assertion\IsComparisonTranspiler;
use webignition\BasilModel\Assertion\AssertionInterface;
use webignition\BasilModel\Assertion\ComparisonAssertion;
use webignition\BasilModelFactory\AssertionFactory;

class IsComparisonTranspilerTest extends AbstractTranspilerTest
{
    use ExcludesAssertionDataProviderTrait;
    use ExistsAssertionDataProviderTrait;
    use IncludesAssertionDataProviderTrait;
    use IsAssertionDataProviderTrait;
    use IsNotAssertionDataProviderTrait;
    use MatchesAssertionDataProviderTrait;
    use NotExistsAssertionDataProviderTrait;
    use UnhandledAssertionDataProviderTrait;

    protected function createTranspiler(): HandlerInterface
    {
        return IsComparisonTranspiler::createTranspiler();
    }

    /**
     * @dataProvider isAssertionDataProvider
     * @dataProvider isNotAssertionDataProvider
     */
    public function testHandlesDoesHandle(AssertionInterface $model)
    {
        $this->assertTrue($this->transpiler->handles($model));
    }

    /**
     * @dataProvider excludesAssertionDataProvider
     * @dataProvider existsAssertionDataProvider
     * @dataProvider includesAssertionDataProvider
     * @dataProvider matchesAssertionDataProvider
     * @dataProvider notExistsAssertionDataProvider
     */
    public function testHandlesDoesNotHandle(object $model)
    {
        $this->assertFalse($this->transpiler->handles($model));
    }

    public function testTranspileWrongComparisonType()
    {
        $assertionFactory = AssertionFactory::createFactory();
        $model = $assertionFactory->createFromAssertionString('".selector" includes "value"');

        $this->expectException(NonTranspilableModelException::class);
        $this->expectExceptionMessage('Non-transpilable model "' . ComparisonAssertion::class . '"');

        $this->transpiler->transpile($model);
    }
}
