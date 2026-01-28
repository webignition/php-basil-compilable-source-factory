<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\ArgumentFactory;
use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
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
                new MethodInvocation(
                    methodName: 'assertCount',
                    arguments: new MethodArguments([
                        $argumentFactory->create(0),
                        $argumentFactory->create(
                            new MethodInvocation(
                                methodName: 'filter',
                                arguments: new MethodArguments([$argumentFactory->create('#hello')]),
                                mightThrow: true,
                                parent: Property::asDependency(DependencyName::PANTHER_CRAWLER),
                            ),
                        ),
                    ]),
                    mightThrow: false,
                    parent: Property::asDependency(DependencyName::PHPUNIT_TEST_CASE),
                )
            ),
            new Statement(
                new MethodInvocation(
                    methodName: 'usleep',
                    arguments: new MethodArguments([$argumentFactory->create(100000)]),
                    mightThrow: false,
                )
            ),
            new Statement(
                new MethodInvocation(
                    methodName: 'assertCount',
                    arguments: new MethodArguments([
                        $argumentFactory->create(1),
                        $argumentFactory->create(
                            new MethodInvocation(
                                methodName: 'filter',
                                arguments: new MethodArguments([$argumentFactory->create('#hello')]),
                                mightThrow: true,
                                parent: Property::asDependency(DependencyName::PANTHER_CRAWLER),
                            )
                        ),
                    ]),
                    mightThrow: false,
                    parent: Property::asDependency(DependencyName::PHPUNIT_TEST_CASE),
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
