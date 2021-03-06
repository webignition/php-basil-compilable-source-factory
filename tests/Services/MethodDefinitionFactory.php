<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

use webignition\BaseBasilTestCase\ClientManager;
use webignition\BasilCompilableSource\Body\Body;
use webignition\BasilCompilableSource\Body\BodyInterface;
use webignition\BasilCompilableSource\ClassName;
use webignition\BasilCompilableSource\EmptyLine;
use webignition\BasilCompilableSource\Expression\CompositeExpression;
use webignition\BasilCompilableSource\Expression\LiteralExpression;
use webignition\BasilCompilableSource\Factory\ArgumentFactory;
use webignition\BasilCompilableSource\MethodArguments\MethodArguments;
use webignition\BasilCompilableSource\MethodDefinition;
use webignition\BasilCompilableSource\MethodDefinitionInterface;
use webignition\BasilCompilableSource\MethodInvocation\ObjectConstructor;
use webignition\BasilCompilableSource\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\MethodInvocation\StaticObjectMethodInvocation;
use webignition\BasilCompilableSource\SingleLineComment;
use webignition\BasilCompilableSource\Statement\Statement;
use webignition\BasilCompilableSource\StaticObject;
use webignition\BasilCompilableSource\VariableDependency;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModels\Test\Configuration;
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
