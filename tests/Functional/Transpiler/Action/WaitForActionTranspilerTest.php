<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\Transpiler\Action;

use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\WaitForActionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\Functional\Transpiler\AbstractTranspilerTest;
use webignition\BasilCompilableSourceFactory\Transpiler\Action\WaitForActionTranspiler;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModel\Action\ActionInterface;

class WaitForActionTranspilerTest extends AbstractTranspilerTest
{
    use WaitForActionFunctionalDataProviderTrait;

    protected function createTranspiler(): HandlerInterface
    {
        return WaitForActionTranspiler::createHandler();
    }

    /**
     * @dataProvider waitForActionFunctionalDataProvider
     */
    public function testTranspileForExecutableActions(
        string $fixture,
        ActionInterface $action,
        array $additionalSetupStatements,
        array $teardownStatements,
        array $additionalVariableIdentifiers
    ) {
        $source = $this->transpiler->createSource($action);

        $variableIdentifiers = array_merge(
            [
                VariableNames::PANTHER_CRAWLER => self::PANTHER_CRAWLER_VARIABLE_NAME,
            ],
            $additionalVariableIdentifiers
        );

        $executableCall = $this->createExecutableCallForRequest(
            $fixture,
            $source,
            $additionalSetupStatements,
            $teardownStatements,
            $variableIdentifiers
        );

        $executableCallStatements = explode("\n", $executableCall);
        $waitForStatement = array_pop($executableCallStatements);

        $executableCallStatements = array_merge($executableCallStatements, [
            '$before = microtime(true);',
            $waitForStatement,
            '$executionDurationInMilliseconds = (microtime(true) - $before) * 1000;',
            '$this->assertGreaterThan(100, $executionDurationInMilliseconds);',
        ]);

        $executableCall = implode("\n", $executableCallStatements);

        eval($executableCall);
    }
}
