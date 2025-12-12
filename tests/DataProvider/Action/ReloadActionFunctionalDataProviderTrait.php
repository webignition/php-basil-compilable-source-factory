<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\ArgumentFactory;
use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilModels\Parser\ActionParser;

trait ReloadActionFunctionalDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function reloadActionFunctionalDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $argumentFactory = ArgumentFactory::createFactory();

        $setupTeardownStatements = new Body([
            new Statement(
                new ObjectMethodInvocation(
                    new VariableDependency(VariableName::PHPUNIT_TEST_CASE),
                    'assertCount',
                    new MethodArguments(
                        $argumentFactory->create(
                            0,
                            new ObjectMethodInvocation(
                                new VariableDependency(VariableName::PANTHER_CRAWLER),
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
                    new VariableDependency(VariableName::PHPUNIT_TEST_CASE),
                    'assertCount',
                    new MethodArguments(
                        $argumentFactory->create(
                            1,
                            new ObjectMethodInvocation(
                                new VariableDependency(VariableName::PANTHER_CRAWLER),
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
