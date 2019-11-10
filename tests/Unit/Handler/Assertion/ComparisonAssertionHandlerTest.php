<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\ComparisonAssertionHandler;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;
use webignition\BasilModel\Assertion\ExaminationAssertion;
use webignition\BasilModelFactory\AssertionFactory;

class ComparisonAssertionHandlerTest extends AbstractTestCase
{
    public function testHandleWrongComparisonType()
    {
        $handler = ComparisonAssertionHandler::createHandler();

        $assertionFactory = AssertionFactory::createFactory();
        $model = $assertionFactory->createFromAssertionString('".selector" exists');

        $this->expectException(UnsupportedModelException::class);
        $this->expectExceptionMessage('Unsupported model "' . ExaminationAssertion::class . '"');

        $handler->handle($model);
    }
}
