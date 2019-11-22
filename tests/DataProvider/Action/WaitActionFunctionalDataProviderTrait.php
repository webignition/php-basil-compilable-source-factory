<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilActionGenerator\ActionGenerator;
use webignition\BasilCompilableSourceFactory\Tests\Services\ResolvedVariableNames;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModel\Action\WaitAction;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Value\DomIdentifierValue;

trait WaitActionFunctionalDataProviderTrait
{
    public function waitActionFunctionalDataProvider(): array
    {
        $actionGenerator = ActionGenerator::createGenerator();
        $fixture = '/action-wait.html';

        return [
            'wait action, literal duration' => [
                'fixture' => $fixture,
                'action' => $actionGenerator->generate('wait 10'),
                'additionalSetupStatements' => null,
                'teardownStatements' => null,
                'additionalVariableIdentifiers' => [
                    'DURATION' => '$duration',
                ],
                'expectedDuration' => 10,
            ],
            'wait action, element value' => [
                'fixture' => $fixture,
                'action' => new WaitAction(
                    'wait $elements.element_name',
                    new DomIdentifierValue(new DomIdentifier('[id="element-value"]'))
                ),
                'additionalSetupStatements' => null,
                'teardownStatements' => null,
                'additionalVariableIdentifiers' => [
                    'DURATION' => '$duration',
                    'HAS' => ResolvedVariableNames::HAS_VARIABLE_NAME,
                ],
                'expectedDuration' => 20,
            ],
            'wait action, attribute value, attribute exists' => [
                'fixture' => $fixture,
                'action' => new WaitAction(
                    'wait $elements.element_name.attribute_name',
                    new DomIdentifierValue(
                        (new DomIdentifier('[id="attribute-value"]'))->withAttributeName('data-duration')
                    )
                ),
                'additionalSetupStatements' => null,
                'teardownStatements' => null,
                'additionalVariableIdentifiers' => [
                    'DURATION' => '$duration',
                    'HAS' => ResolvedVariableNames::HAS_VARIABLE_NAME,
                ],
                'expectedDuration' => 30,
            ],
            'wait action, attribute value, attribute does not exist' => [
                'fixture' => $fixture,
                'action' => new WaitAction(
                    'wait $elements.element_name.attribute_name',
                    new DomIdentifierValue(
                        (new DomIdentifier('[id="attribute-value"]'))->withAttributeName('data-non-existent')
                    )
                ),
                'additionalSetupStatements' => null,
                'teardownStatements' => null,
                'additionalVariableIdentifiers' => [
                    'DURATION' => '$duration',
                    'HAS' => ResolvedVariableNames::HAS_VARIABLE_NAME,
                ],
                'expectedDuration' => 0,
            ],
            'wait action, browser property' => [
                'fixture' => $fixture,
                'action' => $actionGenerator->generate('wait $browser.size'),
                'additionalSetupStatements' => null,
                'teardownStatements' => null,
                'additionalVariableIdentifiers' => [
                    'DURATION' => '$duration',
                    'WEBDRIVER_DIMENSION' => ResolvedVariableNames::WEBDRIVER_DIMENSION_VARIABLE_NAME,
                ],
                'expectedDuration' => 1200,
            ],
            'wait action, page property' => [
                'fixture' => $fixture,
                'action' => $actionGenerator->generate('wait $page.title'),
                'additionalSetupStatements' => null,
                'teardownStatements' => null,
                'additionalVariableIdentifiers' => [
                    'DURATION' => '$duration',
                ],
                'expectedDuration' => 5,
            ],
            'wait action, environment value, value exists' => [
                'fixture' => $fixture,
                'action' => $actionGenerator->generate('wait $env.DURATION'),
                'additionalSetupStatements' => null,
                'teardownStatements' => null,
                'additionalVariableIdentifiers' => [
                    'DURATION' => '$duration',
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => ResolvedVariableNames::ENV_ARRAY_VARIABLE_NAME,
                ],
                'expectedDuration' => 5,
            ],
            'wait action, environment value, value does not exist' => [
                'fixture' => $fixture,
                'action' => $actionGenerator->generate('wait $env.NON_EXISTENT'),
                'additionalSetupStatements' => null,
                'teardownStatements' => null,
                'additionalVariableIdentifiers' => [
                    'DURATION' => '$duration',
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => ResolvedVariableNames::ENV_ARRAY_VARIABLE_NAME,
                ],
                'expectedDuration' => 0,
            ],
        ];
    }
}
