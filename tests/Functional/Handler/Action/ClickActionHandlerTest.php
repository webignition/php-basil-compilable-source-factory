<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\Handler\Action;

use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\ClickActionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\Functional\Handler\AbstractHandlerTest;
use webignition\BasilCompilableSourceFactory\Handler\Action\ClickActionHandler;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\LineList;
use webignition\BasilCompilationSource\MetadataInterface;
use webignition\BasilModel\Action\ActionInterface;

class ClickActionHandlerTest extends AbstractHandlerTest
{
    use ClickActionFunctionalDataProviderTrait;

    protected function createHandler(): HandlerInterface
    {
        return ClickActionHandler::createHandler();
    }

    /**
     * @dataProvider clickActionFunctionalDataProvider
     */
    public function testCreateSourceForExecutableActions(
        string $fixture,
        ActionInterface $action,
        ?LineList $additionalSetupStatements = null,
        ?LineList $teardownStatements = null,
        array $additionalVariableIdentifiers = [],
        ?MetadataInterface $metadata = null
    ) {
        $source = $this->handler->createSource($action);

        $variableIdentifiers = array_merge(
            [
                VariableNames::PANTHER_CRAWLER => self::PANTHER_CRAWLER_VARIABLE_NAME,
            ],
            $additionalVariableIdentifiers
        );

        $code = $this->createExecutableCallForRequest(
            $fixture,
            $source,
            $additionalSetupStatements,
            $teardownStatements,
            $variableIdentifiers,
            $metadata
        );

        eval($code);
    }
}
