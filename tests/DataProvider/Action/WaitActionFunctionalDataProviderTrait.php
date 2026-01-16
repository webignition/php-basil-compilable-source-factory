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
                'statement' => $actionParser->parse('wait 10', 0),
                'additionalVariableIdentifiers' => [],
                'additionalSetupStatements' => null,
                'teardownStatements' => null,
            ],
            'wait action, element value' => [
                'fixture' => $fixture,
                'statement' => $actionParser->parse('wait $"[id=\"element-value\"]"', 0),
                'additionalVariableIdentifiers' => [],
                'additionalSetupStatements' => null,
                'teardownStatements' => null,
            ],
            'wait action, attribute value, attribute exists' => [
                'fixture' => $fixture,
                'statement' => $actionParser->parse('wait $"[id=\"attribute-value\"]".data-duration', 0),
                'additionalVariableIdentifiers' => [],
                'additionalSetupStatements' => null,
                'teardownStatements' => null,
            ],
            'wait action, attribute value, attribute does not exist' => [
                'fixture' => $fixture,
                'statement' => $actionParser->parse('wait $"[id=\"attribute-value\"]".data-non-existent', 0),
                'additionalVariableIdentifiers' => [],
                'additionalSetupStatements' => null,
                'teardownStatements' => null,
            ],
            'wait action, browser property' => [
                'fixture' => $fixture,
                'statement' => $actionParser->parse('wait $browser.size', 0),
                'additionalVariableIdentifiers' => [],
                'additionalSetupStatements' => null,
                'teardownStatements' => null,
            ],
            'wait action, page property' => [
                'fixture' => $fixture,
                'statement' => $actionParser->parse('wait $page.title', 0),
                'additionalVariableIdentifiers' => [],
                'additionalSetupStatements' => null,
                'teardownStatements' => null,
            ],
            'wait action, environment value, value exists' => [
                'fixture' => $fixture,
                'statement' => $actionParser->parse('wait $env.DURATION', 0),
                'additionalVariableIdentifiers' => [
                    VariableName::ENVIRONMENT_VARIABLE_ARRAY->value => ResolvedVariableNames::ENV_ARRAY_VARIABLE_NAME,
                ],
                'additionalSetupStatements' => null,
                'teardownStatements' => null,
            ],
            'wait action, environment value, value does not exist' => [
                'fixture' => $fixture,
                'statement' => $actionParser->parse('wait $env.NON_EXISTENT', 0),
                'additionalVariableIdentifiers' => [
                    VariableName::ENVIRONMENT_VARIABLE_ARRAY->value => ResolvedVariableNames::ENV_ARRAY_VARIABLE_NAME,
                ],
                'additionalSetupStatements' => null,
                'teardownStatements' => null,
            ],
        ];
    }
}
