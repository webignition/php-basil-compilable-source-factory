<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

use webignition\BasilCompilableSource\Block\CodeBlock;
use webignition\BasilCompilableSource\Block\CodeBlockInterface;
use webignition\BasilCompilableSource\ClassDefinition;
use webignition\BasilCompilableSource\ClassDefinitionInterface;
use webignition\BasilCompilableSource\Line\ClassDependency;
use webignition\BasilCompilableSource\MethodDefinition;
use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractGeneratedTestCase;

class ClassDefinitionFactory
{
    public static function createGeneratedBrowserTestForBlock(
        string $fixture,
        CodeBlockInterface $block,
        ?CodeBlockInterface $additionalSetupStatements
    ): ClassDefinitionInterface {
        $methodName = 'test' . md5((string) rand());
        $methodDefinition = new MethodDefinition($methodName, $block);

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

    public static function createPhpUnitTestForBlock(CodeBlock $block): ClassDefinitionInterface
    {
        $methodName = 'testGeneratedCode';
        $methodDefinition = new MethodDefinition($methodName, $block);

        $className = 'Generated' . md5((string) rand()) . 'Test';

        return new ClassDefinition($className, [
            $methodDefinition,
        ]);
    }
}
