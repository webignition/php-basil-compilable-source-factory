<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
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
use webignition\BasilCompilableSourceFactory\Handler\Assertion\ExistenceComparisonHandler;
use webignition\BasilModel\Assertion\AssertionInterface;
use webignition\BasilModel\Assertion\ComparisonAssertion;
use webignition\BasilModel\Assertion\ExaminationAssertion;
use webignition\BasilModelFactory\AssertionFactory;

class ExistenceComparisonHandlerTest extends AbstractHandlerTest
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
        return ExistenceComparisonHandler::createHandler();
    }

    /**
     * @dataProvider existsAssertionDataProvider
     * @dataProvider notExistsAssertionDataProvider
     */
    public function testHandlesDoesHandle(AssertionInterface $model)
    {
        $this->assertTrue($this->handler->handles($model));
    }

    /**
     * @dataProvider excludesAssertionDataProvider
     * @dataProvider includesAssertionDataProvider
     * @dataProvider isAssertionDataProvider
     * @dataProvider isNotAssertionDataProvider
     * @dataProvider matchesAssertionDataProvider
     */
    public function testHandlesDoesNotHandle(object $model)
    {
        $this->assertFalse($this->handler->handles($model));
    }

    public function testHandleWrongComparisonType()
    {
        $assertionFactory = AssertionFactory::createFactory();
        $model = $assertionFactory->createFromAssertionString('".selector" is "value"');

        $this->expectException(UnsupportedModelException::class);
        $this->expectExceptionMessage('Unsupported model "' . ComparisonAssertion::class . '"');

        $this->handler->handle($model);
    }

    /**
     * @dataProvider handleWrongValueTypeDataProvider
     */
    public function testHandleWrongValueType(object $model, string $expectedExceptionMessage)
    {
        $this->expectException(UnsupportedModelException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $this->handler->handle($model);
    }

    public function handleWrongValueTypeDataProvider(): array
    {
        $assertionFactory = AssertionFactory::createFactory();

        return [
            'page element reference' => [
                'model' => $assertionFactory->createFromAssertionString(
                    'page_import_name.elements.element_name exists'
                ),
                'expectedExceptionMessage' => 'Unsupported model "' . ExaminationAssertion::class . '"',
            ],
            'non-scalar object value' => [
                'model' => $assertionFactory->createFromAssertionString(
                    '$data.key exists'
                ),
                'expectedExceptionMessage' => 'Unsupported model "' . ExaminationAssertion::class . '"',
            ],
        ];
    }
}
