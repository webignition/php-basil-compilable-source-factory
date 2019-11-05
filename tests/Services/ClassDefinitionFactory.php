<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

use webignition\BasilCompilationSource\ClassDefinition;
use webignition\BasilCompilationSource\ClassDefinitionInterface;
use webignition\BasilCompilationSource\LineList;
use webignition\BasilCompilationSource\LineListInterface;
use webignition\BasilCompilationSource\MethodDefinition;

class ClassDefinitionFactory
{
    public static function createForLineList(
        string $fixture,
        LineList $lineList,
        ?LineListInterface $additionalSetupStatements
    ): ClassDefinitionInterface {
        $methodName = 'test' . md5((string) rand());
        $methodDefinition = new MethodDefinition($methodName, $lineList);

        $className = 'Generated' . md5((string) rand()) . 'Test';

        return new ClassDefinition($className, [
            MethodDefinitionFactory::createSetUpBeforeClassMethodDefinition($fixture),
            MethodDefinitionFactory::createSetUpMethodDefinition($additionalSetupStatements),
            $methodDefinition
        ]);
    }
}
