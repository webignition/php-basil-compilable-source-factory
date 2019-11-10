<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Value;

use webignition\BasilCompilableSourceFactory\Handler\Value\DataParameterHandler;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Value\BrowserPropertyDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Value\DataParameterValueDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Value\EnvironmentParameterValueDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Value\LiteralValueDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Value\PagePropertyProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Value\UnhandledValueDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\AbstractHandlerTest;
use webignition\BasilModel\Value\ValueInterface;

class DataParameterHandlerTest extends AbstractHandlerTest
{
    use BrowserPropertyDataProviderTrait;
    use DataParameterValueDataProviderTrait;
    use EnvironmentParameterValueDataProviderTrait;
    use LiteralValueDataProviderTrait;
    use PagePropertyProviderTrait;
    use UnhandledValueDataProviderTrait;

    protected function createHandler(): HandlerInterface
    {
        return new DataParameterHandler();
    }

    /**
     * @dataProvider dataParameterValueDataProvider
     */
    public function testHandlesDoesHandle(ValueInterface $model)
    {
        $this->assertTrue($this->handler->handles($model));
    }

    /**
     * @dataProvider browserPropertyDataProvider
     * @dataProvider environmentParameterValueDataProvider
     * @dataProvider literalValueDataProvider
     * @dataProvider pagePropertyDataProvider
     * @dataProvider unhandledValueDataProvider
     */
    public function testHandlesDoesNotHandle(ValueInterface $model)
    {
        $this->assertFalse($this->handler->handles($model));
    }
}
