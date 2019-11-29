<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStepException;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\ClassDefinition\ClassDefinition;
use webignition\BasilCompilationSource\ClassDefinition\ClassDefinitionInterface;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\Line\StatementInterface;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\BasilCompilationSource\MethodDefinition\MethodDefinition;
use webignition\BasilCompilationSource\MethodDefinition\MethodDefinitionInterface;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilDataStructure\Test\Test;

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
     * @param Test $test
     *
     * @return ClassDefinitionInterface
     *
     * @throws UnsupportedStepException
     */
    public function createClassDefinition(Test $test): ClassDefinitionInterface
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

    private function createSetupBeforeClassMethod(Test $test): MethodDefinitionInterface
    {
        $parentCallStatement = new Statement('parent::setUpBeforeClass()');
        $clientRequestStatement = $this->createClientRequestStatement($test);

        $setupBeforeClassMethod = new MethodDefinition('setUpBeforeClass', new CodeBlock([
            $parentCallStatement,
            $clientRequestStatement,
        ]));

        $setupBeforeClassMethod->setStatic();
        $setupBeforeClassMethod->setReturnType('void');

        return $setupBeforeClassMethod;
    }

    private function createClientRequestStatement(Test $test): StatementInterface
    {
        $variableDependencies = new VariablePlaceholderCollection();
        $pantherClientPlaceholder = $variableDependencies->create(VariableNames::PANTHER_CLIENT);

        $clientRequestStatement = new Statement(
            sprintf(
                '%s->request(\'GET\', \'%s\')',
                $pantherClientPlaceholder,
                $test->getConfiguration()->getUrl()
            ),
            (new Metadata())
                ->withVariableDependencies($variableDependencies)
        );

        return $clientRequestStatement;
    }
}
