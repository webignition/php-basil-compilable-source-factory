<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\ExistenceComparisonHandler;
use webignition\BasilModel\Assertion\AssertionInterface;
use webignition\BasilModel\Assertion\ComparisonAssertion;
use webignition\BasilModel\Assertion\ExaminationAssertion;
use webignition\BasilModelFactory\AssertionFactory;

class ExistenceComparisonHandlerTest extends AbstractTestCase
{
    /**
     * @var ExistenceComparisonHandler
     */
    private $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = ExistenceComparisonHandler::createHandler();
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
    public function testHandleWrongValueType(AssertionInterface $assertion, string $expectedExceptionMessage)
    {
        $this->expectException(UnsupportedModelException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $this->handler->handle($assertion);
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
