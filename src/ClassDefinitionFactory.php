<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSource\Body\Body;
use webignition\BasilCompilableSource\ClassDefinition;
use webignition\BasilCompilableSource\ClassDefinitionInterface;
use webignition\BasilCompilableSource\ClassName;
use webignition\BasilCompilableSource\Factory\ArgumentFactory;
use webignition\BasilCompilableSource\MethodDefinition;
use webignition\BasilCompilableSource\MethodDefinitionInterface;
use webignition\BasilCompilableSource\MethodInvocation\ObjectConstructor;
use webignition\BasilCompilableSource\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\MethodInvocation\StaticObjectMethodInvocation;
use webignition\BasilCompilableSource\StaticObject;
use webignition\BasilCompilableSource\VariableDependency;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStepException;
use webignition\BasilModels\Test\Configuration;
use webignition\BasilModels\Test\TestInterface;

class ClassDefinitionFactory
{
    private ClassNameFactory $classNameFactory;
    private StepMethodFactory $stepMethodFactory;
    private ArgumentFactory $argumentFactory;

    public function __construct(
        ClassNameFactory $classNameFactory,
        StepMethodFactory $stepMethodFactory,
        ArgumentFactory $argumentFactory
    ) {
        $this->classNameFactory = $classNameFactory;
        $this->stepMethodFactory = $stepMethodFactory;
        $this->argumentFactory = $argumentFactory;
    }

    public static function createFactory(): ClassDefinitionFactory
    {
        return new ClassDefinitionFactory(
            new ClassNameFactory(),
            StepMethodFactory::createFactory(),
            ArgumentFactory::createFactory()
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

        $stepOrdinalIndex = 1;
        foreach ($test->getSteps() as $stepName => $step) {
            $methodDefinitions[] = $this->stepMethodFactory->create($stepOrdinalIndex, $stepName, $step);
            $stepOrdinalIndex++;
        }

        return new ClassDefinition($this->classNameFactory->create($test), $methodDefinitions);
    }

    private function createSetupBeforeClassMethod(TestInterface $test): MethodDefinitionInterface
    {
        $testConfiguration = $test->getConfiguration();

        $method = new MethodDefinition('setUpBeforeClass', Body::createFromExpressions([
            new StaticObjectMethodInvocation(
                new StaticObject('self'),
                'setBasilTestConfiguration',
                [
                    (new ObjectConstructor(
                        new ClassName(Configuration::class),
                        $this->argumentFactory->create($testConfiguration->getBrowser(), $testConfiguration->getUrl()),
                    ))->withStackedArguments(),
                ],
            ),
            new StaticObjectMethodInvocation(new StaticObject('parent'), 'setUpBeforeClass'),
            new ObjectMethodInvocation(
                new VariableDependency(VariableNames::PANTHER_CLIENT),
                'request',
                $this->argumentFactory->create('GET', $testConfiguration->getUrl())
            ),
            new StaticObjectMethodInvocation(
                new StaticObject('self'),
                'setBasilTestPath',
                $this->argumentFactory->create($test->getPath())
            )
        ]));

        $method->setStatic();
        $method->setReturnType('void');

        return $method;
    }
}
