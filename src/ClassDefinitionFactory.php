<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSource\Block\CodeBlock;
use webignition\BasilCompilableSource\ClassDefinition;
use webignition\BasilCompilableSource\ClassDefinitionInterface;
use webignition\BasilCompilableSource\Line\LiteralExpression;
use webignition\BasilCompilableSource\Line\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\Line\MethodInvocation\StaticObjectMethodInvocation;
use webignition\BasilCompilableSource\Line\Statement\Statement;
use webignition\BasilCompilableSource\MethodDefinition;
use webignition\BasilCompilableSource\MethodDefinitionInterface;
use webignition\BasilCompilableSource\StaticObject;
use webignition\BasilCompilableSource\VariablePlaceholder;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStepException;
use webignition\BasilModels\Test\TestInterface;

class ClassDefinitionFactory
{
    private $classNameFactory;
    private $stepMethodFactory;

    public function __construct(ClassNameFactory $classNameFactory, StepMethodFactory $stepMethodFactory)
    {
        $this->classNameFactory = $classNameFactory;
        $this->stepMethodFactory = $stepMethodFactory;
    }

    public static function createFactory(): ClassDefinitionFactory
    {
        return new ClassDefinitionFactory(
            new ClassNameFactory(),
            StepMethodFactory::createFactory()
        );
    }

    /**
     * @param TestInterface $test
     *
     * @return ClassDefinitionInterface
     *
     * @throws UnsupportedStepException
     */
    public function createClassDefinition(TestInterface $test): ClassDefinitionInterface
    {
        $methodDefinitions = [
            $this->createSetupBeforeClassMethod($test),
        ];

        foreach ($test->getSteps() as $stepName => $step) {
            $stepMethods = $this->stepMethodFactory->createStepMethods($stepName, $step);

            $methodDefinitions[] = $stepMethods->getTestMethod();

            $dataProviderMethod = $stepMethods->getDataProviderMethod();
            if ($dataProviderMethod instanceof MethodDefinitionInterface) {
                $methodDefinitions[] = $dataProviderMethod;
            }
        }

        return new ClassDefinition($this->classNameFactory->create($test), $methodDefinitions);
    }

    private function createSetupBeforeClassMethod(TestInterface $test): MethodDefinitionInterface
    {
        $method = new MethodDefinition('setUpBeforeClass', new CodeBlock([
            new Statement(
                new StaticObjectMethodInvocation(
                    new StaticObject('parent'),
                    'setUpBeforeClass'
                )
            ),
            new Statement(
                new ObjectMethodInvocation(
                    VariablePlaceholder::createDependency(VariableNames::PANTHER_CLIENT),
                    'request',
                    [
                        new LiteralExpression('\'GET\''),
                        new LiteralExpression('\'' . $test->getConfiguration()->getUrl() . '\''),
                    ]
                )
            ),
            new Statement(
                new StaticObjectMethodInvocation(
                    new StaticObject('self'),
                    'setBasilTestPath',
                    [
                        new LiteralExpression('\'' . $test->getPath() . '\''),
                    ]
                )
            ),
        ]));

        $method->setStatic();
        $method->setReturnType('void');

        return $method;
    }
}
