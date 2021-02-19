<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\Tests\Services\ResolvedVariableNames;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilParser\ActionParser;

trait WaitActionFunctionalDataProviderTrait
{
    /**
     * @return array[]
     */
    public function waitActionFunctionalDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $fixture = '/action-wait.html';

        return [
            'wait action, literal duration' => [
                'fixture' => $fixture,
                'action' => $actionParser->parse('wait 10'),
                'additionalSetupStatements' => null,
                'teardownStatements' => null,
                'additionalVariableIdentifiers' => [],
                'expectedDuration' => 10,
            ],
            'wait action, element value' => [
                'fixture' => $fixture,
                'action' => $actionParser->parse('wait $"[id=\"element-value\"]"'),
                'additionalSetupStatements' => null,
                'teardownStatements' => null,
                'additionalVariableIdentifiers' => [],
                'expectedDuration' => 20,
            ],
            'wait action, attribute value, attribute exists' => [
                'fixture' => $fixture,
                'action' => $actionParser->parse('wait $"[id=\"attribute-value\"]".data-duration'),
                'additionalSetupStatements' => null,
                'teardownStatements' => null,
                'additionalVariableIdentifiers' => [],
                'expectedDuration' => 30,
            ],
            'wait action, attribute value, attribute does not exist' => [
                'fixture' => $fixture,
                'action' => $actionParser->parse('wait $"[id=\"attribute-value\"]".data-non-existent'),
                'additionalSetupStatements' => null,
                'teardownStatements' => null,
                'additionalVariableIdentifiers' => [],
                'expectedDuration' => 0,
            ],
            'wait action, browser property' => [
                'fixture' => $fixture,
                'action' => $actionParser->parse('wait $browser.size'),
                'additionalSetupStatements' => null,
                'teardownStatements' => null,
                'additionalVariableIdentifiers' => [],
                'expectedDuration' => 1200,
            ],
            'wait action, page property' => [
                'fixture' => $fixture,
                'action' => $actionParser->parse('wait $page.title'),
                'additionalSetupStatements' => null,
                'teardownStatements' => null,
                'additionalVariableIdentifiers' => [],
                'expectedDuration' => 5,
            ],
            'wait action, environment value, value exists' => [
                'fixture' => $fixture,
                'action' => $actionParser->parse('wait $env.DURATION'),
                'additionalSetupStatements' => null,
                'teardownStatements' => null,
                'additionalVariableIdentifiers' => [
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => ResolvedVariableNames::ENV_ARRAY_VARIABLE_NAME,
                ],
                'expectedDuration' => 5,
            ],
            'wait action, environment value, value does not exist' => [
                'fixture' => $fixture,
                'action' => $actionParser->parse('wait $env.NON_EXISTENT'),
                'additionalSetupStatements' => null,
                'teardownStatements' => null,
                'additionalVariableIdentifiers' => [
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => ResolvedVariableNames::ENV_ARRAY_VARIABLE_NAME,
                ],
                'expectedDuration' => 0,
            ],
        ];
    }
}
