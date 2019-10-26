<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\Handler\Action;

use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\SubmitActionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\Functional\Handler\AbstractHandlerTest;
use webignition\BasilCompilableSourceFactory\Handler\Action\SubmitActionHandler;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\MetadataInterface;
use webignition\BasilModel\Action\ActionInterface;

class SubmitActionHandlerTest extends AbstractHandlerTest
{
    use SubmitActionFunctionalDataProviderTrait;

    protected function createHandler(): HandlerInterface
    {
        return SubmitActionHandler::createHandler();
    }

    /**
     * @dataProvider submitActionFunctionalDataProvider
     */
    public function testCreateSourceForExecutableActions(
        string $fixture,
        ActionInterface $action,
        array $additionalSetupStatements,
        array $teardownStatements,
        array $additionalVariableIdentifiers,
        ?MetadataInterface $metadata = null
    ) {
        $lineList = $this->handler->createSource($action);

        $variableIdentifiers = array_merge(
            [
                VariableNames::PANTHER_CRAWLER => self::PANTHER_CRAWLER_VARIABLE_NAME,
            ],
            $additionalVariableIdentifiers
        );

        $executableCall = $this->createExecutableCallForRequest(
            $fixture,
            $lineList,
            $additionalSetupStatements,
            $teardownStatements,
            $variableIdentifiers,
            $metadata
        );

        eval($executableCall);
    }
}
