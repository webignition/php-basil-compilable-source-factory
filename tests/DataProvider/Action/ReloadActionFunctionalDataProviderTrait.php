<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSource\Body\Body;
use webignition\BasilCompilableSource\Factory\ArgumentFactory;
use webignition\BasilCompilableSource\MethodArguments\MethodArguments;
use webignition\BasilCompilableSource\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSource\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\Statement\Statement;
use webignition\BasilCompilableSource\VariableDependency;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilParser\ActionParser;

trait ReloadActionFunctionalDataProviderTrait
{
    /**
     * @return array[]
     */
    public function reloadActionFunctionalDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $argumentFactory = ArgumentFactory::createFactory();

        $setupTeardownStatements = new Body([
            new Statement(
                new ObjectMethodInvocation(
                    new VariableDependency(VariableNames::PHPUNIT_TEST_CASE),
                    'assertCount',
                    new MethodArguments(
                        $argumentFactory->create(
                            0,
                            new ObjectMethodInvocation(
                                new VariableDependency(VariableNames::PANTHER_CRAWLER),
                                'filter',
                                new MethodArguments($argumentFactory->create('#hello'))
                            ),
                        )
                    )
                )
            ),
            new Statement(
                new MethodInvocation(
                    'usleep',
                    new MethodArguments($argumentFactory->create(100000))
                )
            ),
            new Statement(
                new ObjectMethodInvocation(
                    new VariableDependency(VariableNames::PHPUNIT_TEST_CASE),
                    'assertCount',
                    new MethodArguments(
                        $argumentFactory->create(
                            1,
                            new ObjectMethodInvocation(
                                new VariableDependency(VariableNames::PANTHER_CRAWLER),
                                'filter',
                                new MethodArguments($argumentFactory->create('#hello'))
                            )
                        )
                    )
                )
            ),
        ]);

        return [
            'reload action' => [
                'fixture' => '/action-wait-for.html',
                'action' => $actionParser->parse('reload'),
                'additionalSetupStatements' => $setupTeardownStatements,
                'teardownStatements' => $setupTeardownStatements,
            ],
        ];
    }
}
