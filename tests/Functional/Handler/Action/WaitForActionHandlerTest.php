<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\Handler\Action;

use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\WaitForActionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\Functional\Handler\AbstractHandlerTest;
use webignition\BasilCompilableSourceFactory\Handler\Action\WaitForActionHandler;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\LineList;
use webignition\BasilModel\Action\ActionInterface;

class WaitForActionHandlerTest extends AbstractHandlerTest
{
    use WaitForActionFunctionalDataProviderTrait;

    protected function createHandler(): HandlerInterface
    {
        return WaitForActionHandler::createHandler();
    }

    /**
     * @dataProvider waitForActionFunctionalDataProvider
     */
    public function testCreateSourceForExecutableActions(
        string $fixture,
        ActionInterface $action,
        ?LineList $additionalSetupStatements = null,
        ?LineList $teardownStatements = null,
        array $additionalVariableIdentifiers = []
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
            $variableIdentifiers
        );

        $executableCallStatements = explode("\n", $code);
        $waitForStatement = array_pop($executableCallStatements);

        $executableCallStatements = array_merge($executableCallStatements, [
            '$before = microtime(true);',
            $waitForStatement,
            '$executionDurationInMilliseconds = (microtime(true) - $before) * 1000;',
            '$this->assertGreaterThan(100, $executionDurationInMilliseconds);',
        ]);

        $code = implode("\n", $executableCallStatements);

        eval($code);
    }
}
