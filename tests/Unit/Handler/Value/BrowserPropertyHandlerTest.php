<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Value;

use webignition\BasilCompilableSourceFactory\Exception\UnknownObjectPropertyException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Value\BrowserPropertyDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Value\EnvironmentParameterValueDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Value\LiteralValueDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Value\PagePropertyProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Value\UnhandledValueDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\AbstractHandlerTest;
use webignition\BasilCompilableSourceFactory\Handler\Value\BrowserPropertyHandler;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\ObjectValueType;
use webignition\BasilModel\Value\ValueInterface;

class BrowserPropertyHandlerTest extends AbstractHandlerTest
{
    use BrowserPropertyDataProviderTrait;
    use EnvironmentParameterValueDataProviderTrait;
    use LiteralValueDataProviderTrait;
    use PagePropertyProviderTrait;
    use UnhandledValueDataProviderTrait;

    protected function createTranspiler(): HandlerInterface
    {
        return BrowserPropertyHandler::createHandler();
    }

    /**
     * @dataProvider browserPropertyDataProvider
     */
    public function testHandlesDoesHandle(ValueInterface $model)
    {
        $this->assertTrue($this->transpiler->handles($model));
    }

    /**
     * @dataProvider environmentParameterValueDataProvider
     * @dataProvider literalValueDataProvider
     * @dataProvider pagePropertyDataProvider
     * @dataProvider unhandledValueDataProvider
     */
    public function testHandlesDoesNotHandle(ValueInterface $model)
    {
        $this->assertFalse($this->transpiler->handles($model));
    }

    public function testTranspileThrowsUnknownObjectPropertyException()
    {
        $model = new ObjectValue(ObjectValueType::BROWSER_PROPERTY, '$browser.foo', 'foo');

        $this->expectException(UnknownObjectPropertyException::class);
        $this->expectExceptionMessage('Unknown object property "foo"');

        $this->transpiler->createSource($model);
    }
}
