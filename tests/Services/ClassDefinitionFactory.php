<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\ClassBody;
use webignition\BasilCompilableSourceFactory\Model\ClassDefinition;
use webignition\BasilCompilableSourceFactory\Model\ClassDefinitionInterface;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\ClassSignature;
use webignition\BasilCompilableSourceFactory\Model\MethodDefinition;
use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractGeneratedTestCase;

class ClassDefinitionFactory
{
    public static function createGeneratedBrowserTestForBody(
        string $fixture,
        BodyInterface $body,
    ): ClassDefinitionInterface {
        $methodName = 'test' . md5((string) rand());
        $methodDefinition = new MethodDefinition($methodName, $body);

        $className = 'Generated' . md5((string) rand()) . 'Test';

        return new ClassDefinition(
            new ClassSignature(
                $className,
                new ClassName(AbstractGeneratedTestCase::class)
            ),
            new ClassBody([
                MethodDefinitionFactory::createSetUpBeforeClassMethodDefinition($fixture),
                $methodDefinition
            ])
        );
    }
}
