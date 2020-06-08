<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

use webignition\BasilCompilableSource\Body\Body;
use webignition\BasilCompilableSource\Body\BodyInterface;
use webignition\BasilCompilableSource\Expression\CompositeExpression;
use webignition\BasilCompilableSource\EmptyLine;
use webignition\BasilCompilableSource\Expression\LiteralExpression;
use webignition\BasilCompilableSource\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\MethodInvocation\StaticObjectMethodInvocation;
use webignition\BasilCompilableSource\SingleLineComment;
use webignition\BasilCompilableSource\Statement\Statement;
use webignition\BasilCompilableSource\MethodDefinition;
use webignition\BasilCompilableSource\MethodDefinitionInterface;
use webignition\BasilCompilableSource\StaticObject;
use webignition\BasilCompilableSource\VariableDependency;
use webignition\BasilCompilableSourceFactory\VariableNames;
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

        $body = new Body([
            new SingleLineComment('Test harness lines'),
            new Statement(new LiteralExpression('parent::setUpBeforeClass()')),
            new Statement(
                new ObjectMethodInvocation(
                    new VariableDependency(VariableNames::PANTHER_CLIENT),
                    'request',
                    [
                        new LiteralExpression('\'GET\''),
                        $requestUriExpression,
                    ]
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
