<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Transpiler\Action;

use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\BackActionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\ClickActionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\ForwardActionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\ReloadActionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\SetActionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\SubmitActionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\UnhandledActionsDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\WaitActionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\WaitForActionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Transpiler\AbstractTranspilerTest;
use webignition\BasilCompilableSourceFactory\Transpiler\Action\BrowserOperationActionTranspiler;
use webignition\BasilCompilableSourceFactory\Transpiler\TranspilerInterface;
use webignition\BasilModel\Action\ActionInterface;

class BrowserOperationActionTranspilerTest extends AbstractTranspilerTest
{
    use WaitActionDataProviderTrait;
    use WaitForActionDataProviderTrait;
    use UnhandledActionsDataProviderTrait;
    use BackActionDataProviderTrait;
    use ForwardActionDataProviderTrait;
    use ReloadActionDataProviderTrait;
    use ClickActionDataProviderTrait;
    use SubmitActionDataProviderTrait;
    use SetActionDataProviderTrait;

    protected function createTranspiler(): TranspilerInterface
    {
        return BrowserOperationActionTranspiler::createTranspiler();
    }

    /**
     * @dataProvider backActionDataProvider
     * @dataProvider forwardActionDataProvider
     * @dataProvider reloadActionDataProvider
     */
    public function testHandlesDoesHandle(ActionInterface $model)
    {
        $this->assertTrue($this->transpiler->handles($model));
    }

    /**
     * @dataProvider waitForActionDataProvider
     * @dataProvider waitActionDataProvider
     * @dataProvider clickActionDataProvider
     * @dataProvider submitActionDataProvider
     * @dataProvider setActionDataProvider
     * @dataProvider unhandledActionsDataProvider
     */
    public function testHandlesDoesNotHandle(object $model)
    {
        $this->assertFalse($this->transpiler->handles($model));
    }
}
