<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Transpiler\Action;

use webignition\BasilCompilableSourceFactory\Exception\NonTranspilableModelException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
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
use webignition\BasilCompilableSourceFactory\Transpiler\Action\WaitActionTranspiler;
use webignition\BasilModel\Action\ActionInterface;
use webignition\BasilModel\Action\WaitAction;
use webignition\BasilModel\Value\PageElementReference;

class WaitActionTranspilerTest extends AbstractTranspilerTest
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

    protected function createTranspiler(): HandlerInterface
    {
        return WaitActionTranspiler::createHandler();
    }

    /**
     * @dataProvider waitActionDataProvider
     */
    public function testHandlesDoesHandle(ActionInterface $model)
    {
        $this->assertTrue($this->transpiler->handles($model));
    }

    /**
     * @dataProvider waitForActionDataProvider
     * @dataProvider backActionDataProvider
     * @dataProvider forwardActionDataProvider
     * @dataProvider reloadActionDataProvider
     * @dataProvider clickActionDataProvider
     * @dataProvider submitActionDataProvider
     * @dataProvider setActionDataProvider
     * @dataProvider unhandledActionsDataProvider
     */
    public function testHandlesDoesNotHandle(object $model)
    {
        $this->assertFalse($this->transpiler->handles($model));
    }

    public function testTranspileWithNonTranspilableValue()
    {
        $action = new WaitAction(
            'wait 30',
            new PageElementReference(
                'page_import_name.elements.element_name',
                'page_import_name',
                'element_name'
            )
        );

        $this->expectException(NonTranspilableModelException::class);

        $this->transpiler->createSource($action);
    }
}
