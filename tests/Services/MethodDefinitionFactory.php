<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

use webignition\BaseBasilTestCase\ClientManager;
use webignition\BasilCompilableSourceFactory\ArgumentFactory;
use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\EmptyLine;
use webignition\BasilCompilableSourceFactory\Model\Expression\CompositeExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodDefinition;
use webignition\BasilCompilableSourceFactory\Model\MethodDefinitionInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectConstructor;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Model\SingleLineComment;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\StaticObject;
use webignition\SymfonyPantherWebServerRunner\Options;

class MethodDefinitionFactory
{
    public static function createSetUpBeforeClassMethodDefinition(string $fixture): MethodDefinitionInterface
    {
        $requestBaseUri = new MethodInvocation(
            methodName: 'getBaseUri',
            arguments: new MethodArguments(),
            mightThrow: false,
            parent: new StaticObject(Options::class),
        );

        $requestUriExpression = new CompositeExpression([
            $requestBaseUri,
            new LiteralExpression(' . \'' . $fixture . '\''),
        ]);

        $argumentFactory = ArgumentFactory::createFactory();

        $body = new Body([
            new Statement(
                new MethodInvocation(
                    methodName: 'setClientManager',
                    arguments: new MethodArguments([
                        new ObjectConstructor(
                            class: new ClassName(ClientManager::class),
                            arguments: new MethodArguments($argumentFactory->create('chrome')),
                            mightThrow: false,
                        ),
                    ]),
                    mightThrow: false,
                    parent: new StaticObject('self'),
                )
            ),
            new SingleLineComment('Test harness lines'),
            new Statement(new LiteralExpression('parent::setUpBeforeClass()')),
            new Statement(
                new MethodInvocation(
                    methodName: 'request',
                    arguments: new MethodArguments($argumentFactory->create('GET', $requestUriExpression)),
                    mightThrow: false,
                    parent: Property::asDependency(DependencyName::PANTHER_CLIENT),
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
