<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\ArgumentFactory;
use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
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
                    object: new VariableDependency(DependencyName::PHPUNIT_TEST_CASE->value),
                    methodName: 'assertCount',
                    arguments: new MethodArguments(
                        $argumentFactory->create(
                            0,
                            new ObjectMethodInvocation(
                                object: new VariableDependency(DependencyName::PANTHER_CRAWLER->value),
                                methodName: 'filter',
                                arguments: new MethodArguments($argumentFactory->create('#hello')),
                                mightThrow: true,
                            ),
                        )
                    ),
                    mightThrow: false,
                )
            ),
            new Statement(
                new MethodInvocation(
                    methodName: 'usleep',
                    arguments: new MethodArguments($argumentFactory->create(100000)),
                    mightThrow: false,
                )
            ),
            new Statement(
                new ObjectMethodInvocation(
                    object: new VariableDependency(DependencyName::PHPUNIT_TEST_CASE->value),
                    methodName: 'assertCount',
                    arguments: new MethodArguments(
                        $argumentFactory->create(
                            1,
                            new ObjectMethodInvocation(
                                object: new VariableDependency(DependencyName::PANTHER_CRAWLER->value),
                                methodName: 'filter',
                                arguments: new MethodArguments($argumentFactory->create('#hello')),
                                mightThrow: true,
                            )
                        )
                    ),
                    mightThrow: false,
                )
            ),
        ]);

        return [
            'reload action' => [
                'fixture' => '/action-wait-for.html',
                'statement' => $actionParser->parse('reload', 0),
                'additionalVariableIdentifiers' => [],
                'additionalSetupStatements' => $setupTeardownStatements,
                'teardownStatements' => $setupTeardownStatements,
            ],
        ];
    }
}
