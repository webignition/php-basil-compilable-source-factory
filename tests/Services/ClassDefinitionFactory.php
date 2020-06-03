<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

use webignition\BasilCompilableSource\Block\CodeBlockInterface;
use webignition\BasilCompilableSource\ClassDefinition;
use webignition\BasilCompilableSource\ClassDefinitionInterface;
use webignition\BasilCompilableSource\Line\ClassDependency;
use webignition\BasilCompilableSource\MethodDefinition;
use webignition\BasilCompilableSource\SourceInterface;
use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractGeneratedTestCase;

class ClassDefinitionFactory
{
    /**
     * @param string $fixture
     * @param SourceInterface[] $sources
     * @param CodeBlockInterface|null $additionalSetupStatements
     *
     * @return ClassDefinitionInterface
     */
    public static function createGeneratedBrowserTestForBlock(
        string $fixture,
        array $sources,
        ?CodeBlockInterface $additionalSetupStatements
    ): ClassDefinitionInterface {
        $methodName = 'test' . md5((string) rand());
        $methodDefinition = new MethodDefinition($methodName, $sources);

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
