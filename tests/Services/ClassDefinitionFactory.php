<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

use webignition\BasilCompilableSource\Body\BodyInterface;
use webignition\BasilCompilableSource\ClassDefinition;
use webignition\BasilCompilableSource\ClassDefinitionInterface;
use webignition\BasilCompilableSource\Expression\ClassDependency;
use webignition\BasilCompilableSource\MethodDefinition;
use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractGeneratedTestCase;

class ClassDefinitionFactory
{
    public static function createGeneratedBrowserTestForBlock(
        string $fixture,
        BodyInterface $body,
        ?BodyInterface $additionalSetupStatements
    ): ClassDefinitionInterface {
        $methodName = 'test' . md5((string) rand());
        $methodDefinition = new MethodDefinition($methodName, $body);

        $className = 'Generated' . md5((string) rand()) . 'Test';

        $classDefinition = new ClassDefinition(
            $className,
            [
                MethodDefinitionFactory::createSetUpBeforeClassMethodDefinition($fixture),
                MethodDefinitionFactory::createSetUpMethodDefinition($additionalSetupStatements),
                $methodDefinition
            ]
        );

        $classDefinition->setBaseClass(new ClassDependency(AbstractGeneratedTestCase::class));

        return $classDefinition;
    }
}
