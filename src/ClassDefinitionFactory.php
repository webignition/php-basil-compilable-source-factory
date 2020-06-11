<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSource\Body\Body;
use webignition\BasilCompilableSource\ClassDefinition;
use webignition\BasilCompilableSource\ClassDefinitionInterface;
use webignition\BasilCompilableSource\Expression\LiteralExpression;
use webignition\BasilCompilableSource\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\MethodInvocation\StaticObjectMethodInvocation;
use webignition\BasilCompilableSource\Statement\Statement;
use webignition\BasilCompilableSource\MethodDefinition;
use webignition\BasilCompilableSource\MethodDefinitionInterface;
use webignition\BasilCompilableSource\StaticObject;
use webignition\BasilCompilableSource\VariableDependency;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStepException;
use webignition\BasilModels\Test\TestInterface;

class ClassDefinitionFactory
{
    private ClassNameFactory $classNameFactory;
    private StepMethodFactory $stepMethodFactory;

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
            $methodDefinitions[] = $this->stepMethodFactory->create($stepName, $step);
        }

        return new ClassDefinition($this->classNameFactory->create($test), $methodDefinitions);
    }

    private function createSetupBeforeClassMethod(TestInterface $test): MethodDefinitionInterface
    {
        $method = new MethodDefinition('setUpBeforeClass', new Body([
            new Statement(
                new StaticObjectMethodInvocation(
                    new StaticObject('parent'),
                    'setUpBeforeClass'
                )
            ),
            new Statement(
                new ObjectMethodInvocation(
                    new VariableDependency(VariableNames::PANTHER_CLIENT),
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
