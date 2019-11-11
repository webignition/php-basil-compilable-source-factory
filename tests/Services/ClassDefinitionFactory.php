<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\ClassDefinition\ClassDefinition;
use webignition\BasilCompilationSource\ClassDefinition\ClassDefinitionInterface;
use webignition\BasilCompilationSource\MethodDefinition\MethodDefinition;

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

        return new ClassDefinition($className, [
            MethodDefinitionFactory::createSetUpBeforeClassMethodDefinition($fixture),
            MethodDefinitionFactory::createSetUpMethodDefinition($additionalSetupStatements),
            $methodDefinition
        ]);
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
