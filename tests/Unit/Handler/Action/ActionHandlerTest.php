<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Action;

use webignition\BasilCompilableSource\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStatementException;
use webignition\BasilCompilableSourceFactory\Handler\Action\ActionHandler;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\CreateFromBackActionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\CreateFromClickActionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\CreateFromForwardActionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\CreateFromReloadActionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\CreateFromSetActionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\CreateFromSubmitActionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\CreateFromWaitActionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\CreateFromWaitForActionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractResolvableTest;
use webignition\BasilModels\Action\ActionInterface;
use webignition\BasilParser\ActionParser;

class ActionHandlerTest extends AbstractResolvableTest
{
    use CreateFromBackActionDataProviderTrait;
    use CreateFromClickActionDataProviderTrait;
    use CreateFromForwardActionDataProviderTrait;
    use CreateFromReloadActionDataProviderTrait;
    use CreateFromSetActionDataProviderTrait;
    use CreateFromSubmitActionDataProviderTrait;
    use CreateFromWaitActionDataProviderTrait;
    use CreateFromWaitForActionDataProviderTrait;

    /**
     * @dataProvider createFromBackActionDataProvider
     * @dataProvider createFromClickActionDataProvider
     * @dataProvider createFromForwardActionDataProvider
     * @dataProvider createFromReloadActionDataProvider
     * @dataProvider createFromSetActionDataProvider
     * @dataProvider createFromSubmitActionDataProvider
     * @dataProvider createFromWaitActionDataProvider
     * @dataProvider createFromWaitForActionDataProvider
     */
    public function testHandleSuccess(
        ActionInterface $action,
        string $expectedRenderedSource,
        MetadataInterface $expectedMetadata
    ): void {
        $handler = ActionHandler::createHandler();
        $source = $handler->handle($action);

        $this->assertRenderResolvable($expectedRenderedSource, $source);
        $this->assertEquals($expectedMetadata, $source->getMetadata());
    }

    /**
     * @dataProvider handleThrowsExceptionDataProvider
     */
    public function testHandleThrowsException(
        ActionInterface $action,
        UnsupportedStatementException $expectedException
    ): void {
        $handler = ActionHandler::createHandler();
        $this->expectExceptionObject($expectedException);

        $handler->handle($action);
    }

    /**
     * @return array[]
     */
    public function handleThrowsExceptionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'interaction action, identifier not dom identifier' => [
                'action' => $actionParser->parse('click $elements.element_name'),
                'expectedException' => new UnsupportedStatementException(
                    $actionParser->parse('click $elements.element_name'),
                    new UnsupportedContentException(
                        UnsupportedContentException::TYPE_IDENTIFIER,
                        '$elements.element_name'
                    )
                ),
            ],
            'unsupported action type' => [
                'action' => $actionParser->parse('foo $".selector"'),
                'expectedException' => new UnsupportedStatementException(
                    $actionParser->parse('foo $".selector"')
                ),
            ],
        ];
    }
}
