<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

use webignition\BasilCompilationSource\Block\Block;
use webignition\BasilCompilationSource\Block\BlockInterface;
use webignition\BasilCompilationSource\ClassDefinition\ClassDefinition;
use webignition\BasilCompilationSource\ClassDefinition\ClassDefinitionInterface;
use webignition\BasilCompilationSource\MethodDefinition\MethodDefinition;

class ClassDefinitionFactory
{
    public static function createGeneratedBrowserTestForBlock(
        string $fixture,
        Block $block,
        ?BlockInterface $additionalSetupStatements
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

    public static function createPhpUnitTestForBlock(Block $block): ClassDefinitionInterface
    {
        $methodName = 'testGeneratedCode';
        $methodDefinition = new MethodDefinition($methodName, $block);

        $className = 'Generated' . md5((string) rand()) . 'Test';

        return new ClassDefinition($className, [
            $methodDefinition,
        ]);
    }
}
