<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\Handler\Action;

use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\WaitActionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\Functional\Handler\AbstractHandlerTest;
use webignition\BasilCompilableSourceFactory\Handler\Action\WaitActionHandler;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\MetadataInterface;
use webignition\BasilModel\Action\ActionInterface;

class WaitActionHandlerTest extends AbstractHandlerTest
{
    use WaitActionFunctionalDataProviderTrait;

    protected function createHandler(): HandlerInterface
    {
        return WaitActionHandler::createHandler();
    }

    /**
     * @dataProvider waitActionFunctionalDataProvider
     */
    public function testCreateSourceForExecutableActions(
        string $fixture,
        ActionInterface $action,
        array $additionalSetupStatements,
        array $teardownStatements,
        array $additionalVariableIdentifiers,
        MetadataInterface $metadata,
        int $expectedDuration
    ) {
        $lineList = $this->handler->createSource($action);

        $expectedDurationThreshold = $expectedDuration + 1;

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

        $executableCallStatements = explode("\n", $executableCall);
        $sleepStatement = array_pop($executableCallStatements);

        $executableCallStatements = array_merge($executableCallStatements, [
            '$before = microtime(true);',
            $sleepStatement,
            '$executionDurationInMilliseconds = (microtime(true) - $before) * 1000;',
            '$this->assertGreaterThan(' . $expectedDuration . ', $executionDurationInMilliseconds);',
            '$this->assertLessThan(' . $expectedDurationThreshold . ', $executionDurationInMilliseconds);',
        ]);

        $executableCall = implode("\n", $executableCallStatements);

        eval($executableCall);
    }
}
