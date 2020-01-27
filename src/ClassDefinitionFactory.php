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
            $this->createSetupMethod($test),
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
            new Statement('parent::setUpBeforeClass()'),
            $this->createClientRequestStatement($test),
        ]));

        $method->setStatic();
        $method->setReturnType('void');

        return $method;
    }

    private function createClientRequestStatement(TestInterface $test): StatementInterface
    {
        $variableDependencies = new VariablePlaceholderCollection();
        $pantherClientPlaceholder = $variableDependencies->create(VariableNames::PANTHER_CLIENT);

        return new Statement(
            sprintf(
                '%s->request(\'GET\', \'%s\')',
                $pantherClientPlaceholder,
                $test->getConfiguration()->getUrl()
            ),
            (new Metadata())
                ->withVariableDependencies($variableDependencies)
        );
    }

    private function createSetupMethod(TestInterface $test): MethodDefinitionInterface
    {
        $setupBeforeClassMethod = new MethodDefinition('setUp', new CodeBlock([
            new Statement('parent::setUp()'),
            $this->createSetBasilTestPathStatement($test),
        ]));

        $setupBeforeClassMethod->setProtected();
        $setupBeforeClassMethod->setReturnType('void');

        return $setupBeforeClassMethod;
    }

    private function createSetBasilTestPathStatement(TestInterface $test): StatementInterface
    {
        $variableDependencies = new VariablePlaceholderCollection();
        $phpUnitPlaceholder = $variableDependencies->create(VariableNames::PHPUNIT_TEST_CASE);

        return new Statement(
            sprintf(
                '%s->setBasilTestPath(\'%s\')',
                $phpUnitPlaceholder,
                $test->getPath()
            ),
            (new Metadata())
                ->withVariableDependencies($variableDependencies)
        );
    }
}
