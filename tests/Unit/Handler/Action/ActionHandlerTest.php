<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Action;

use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\CreateFromBackActionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\CreateFromClickActionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\CreateFromForwardActionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\CreateFromReloadActionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\CreateFromSetActionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\CreateFromSubmitActionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\CreateFromWaitActionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\CreateFromWaitForActionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Handler\Action\ActionHandler;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\Metadata\MetadataInterface;
use webignition\BasilDataStructure\Action\ActionInterface;

/**
 * @group poc208
 */
class ActionHandlerTest extends AbstractTestCase
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
    public function testHandle(
        ActionInterface $action,
        CodeBlockInterface $expectedContent,
        MetadataInterface $expectedMetadata
    ) {
        $handler = ActionHandler::createHandler();
        $source = $handler->handle($action);

        $this->assertBlockContentEquals($expectedContent, $source);
        $this->assertMetadataEquals($expectedMetadata, $source->getMetadata());
    }
}
