<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Tests\Services\ResolvedVariableNames;
use webignition\BasilModels\Parser\ActionParser;

trait WaitActionFunctionalDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function waitActionFunctionalDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $fixture = '/action-wait.html';

        return [
            'wait action, literal duration' => [
                'fixture' => $fixture,
                'action' => $actionParser->parse('wait 10', 0),
                'additionalSetupStatements' => null,
                'teardownStatements' => null,
                'additionalVariableIdentifiers' => [],
            ],
            'wait action, element value' => [
                'fixture' => $fixture,
                'action' => $actionParser->parse('wait $"[id=\"element-value\"]"', 0),
                'additionalSetupStatements' => null,
                'teardownStatements' => null,
                'additionalVariableIdentifiers' => [],
            ],
            'wait action, attribute value, attribute exists' => [
                'fixture' => $fixture,
                'action' => $actionParser->parse('wait $"[id=\"attribute-value\"]".data-duration', 0),
                'additionalSetupStatements' => null,
                'teardownStatements' => null,
                'additionalVariableIdentifiers' => [],
            ],
            'wait action, attribute value, attribute does not exist' => [
                'fixture' => $fixture,
                'action' => $actionParser->parse('wait $"[id=\"attribute-value\"]".data-non-existent', 0),
                'additionalSetupStatements' => null,
                'teardownStatements' => null,
                'additionalVariableIdentifiers' => [],
            ],
            'wait action, browser property' => [
                'fixture' => $fixture,
                'action' => $actionParser->parse('wait $browser.size', 0),
                'additionalSetupStatements' => null,
                'teardownStatements' => null,
                'additionalVariableIdentifiers' => [],
            ],
            'wait action, page property' => [
                'fixture' => $fixture,
                'action' => $actionParser->parse('wait $page.title', 0),
                'additionalSetupStatements' => null,
                'teardownStatements' => null,
                'additionalVariableIdentifiers' => [],
            ],
            'wait action, environment value, value exists' => [
                'fixture' => $fixture,
                'action' => $actionParser->parse('wait $env.DURATION', 0),
                'additionalSetupStatements' => null,
                'teardownStatements' => null,
                'additionalVariableIdentifiers' => [
                    VariableName::ENVIRONMENT_VARIABLE_ARRAY->value => ResolvedVariableNames::ENV_ARRAY_VARIABLE_NAME,
                ],
            ],
            'wait action, environment value, value does not exist' => [
                'fixture' => $fixture,
                'action' => $actionParser->parse('wait $env.NON_EXISTENT', 0),
                'additionalSetupStatements' => null,
                'teardownStatements' => null,
                'additionalVariableIdentifiers' => [
                    VariableName::ENVIRONMENT_VARIABLE_ARRAY->value => ResolvedVariableNames::ENV_ARRAY_VARIABLE_NAME,
                ],
            ],
        ];
    }
}
