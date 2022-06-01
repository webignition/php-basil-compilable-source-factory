<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

use webignition\BaseBasilTestCase\ClientManager;
use webignition\BasilCompilableSourceFactory\ArgumentFactory;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\EmptyLine;
use webignition\BasilCompilableSourceFactory\Model\Expression\CompositeExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodDefinition;
use webignition\BasilCompilableSourceFactory\Model\MethodDefinitionInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectConstructor;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\StaticObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\SingleLineComment;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\StaticObject;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModels\Model\Test\Configuration;
use webignition\SymfonyPantherWebServerRunner\Options;

class MethodDefinitionFactory
{
    public static function createSetUpBeforeClassMethodDefinition(string $fixture): MethodDefinitionInterface
    {
        $requestBaseUri = new StaticObjectMethodInvocation(
            new StaticObject(Options::class),
            'getBaseUri'
        );

        $requestUriExpression = new CompositeExpression([
            $requestBaseUri,
            new LiteralExpression(' . \'' . $fixture . '\''),
        ]);

        $argumentFactory = ArgumentFactory::createFactory();

        $body = new Body([
            new Statement(
                new StaticObjectMethodInvocation(
                    new StaticObject('self'),
                    'setClientManager',
                    new MethodArguments([
                        new ObjectConstructor(
                            new ClassName(ClientManager::class),
                            new MethodArguments(
                                [
                                    new ObjectConstructor(
                                        new ClassName(Configuration::class),
                                        new MethodArguments($argumentFactory->create('chrome', $requestBaseUri))
                                    ),
                                ],
                                MethodArguments::FORMAT_STACKED
                            )
                        ),
                    ]),
                )
            ),
            new SingleLineComment('Test harness lines'),
            new Statement(new LiteralExpression('parent::setUpBeforeClass()')),
            new Statement(
                new ObjectMethodInvocation(
                    new VariableDependency(VariableNames::PANTHER_CLIENT),
                    'request',
                    new MethodArguments($argumentFactory->create('GET', $requestUriExpression))
                )
            ),
        ]);

        $methodDefinition = new MethodDefinition('setUpBeforeClass', $body);
        $methodDefinition->setStatic();
        $methodDefinition->setReturnType('void');

        return $methodDefinition;
    }

    public static function createSetUpMethodDefinition(
        ?BodyInterface $additionalSetupStatements
    ): MethodDefinitionInterface {
        if (null === $additionalSetupStatements) {
            $additionalSetupStatements = new Body([]);
        }

        $body = new Body([
            new SingleLineComment('Test harness lines'),
            new Statement(new LiteralExpression('parent::setUp()')),
            new EmptyLine(),
            new SingleLineComment('Additional setup statements'),
            $additionalSetupStatements,
        ]);

        $methodDefinition = new MethodDefinition('setUp', $body);
        $methodDefinition->setProtected();
        $methodDefinition->setReturnType('void');

        return $methodDefinition;
    }
}
