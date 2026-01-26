<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BaseBasilTestCase\ClientManager;
use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStepException;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\ClassBody;
use webignition\BasilCompilableSourceFactory\Model\ClassDefinition;
use webignition\BasilCompilableSourceFactory\Model\ClassDefinitionInterface;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\ClassSignature;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodDefinition;
use webignition\BasilCompilableSourceFactory\Model\MethodDefinitionInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectConstructor;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\StaticObject;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilModels\Model\Test\NamedTestInterface;
use webignition\BasilModels\Model\Test\TestInterface;

class ClassDefinitionFactory
{
    public function __construct(
        private ClassNameFactory $classNameFactory,
        private StepMethodFactory $stepMethodFactory,
        private ArgumentFactory $argumentFactory
    ) {}

    public static function createFactory(): ClassDefinitionFactory
    {
        return new ClassDefinitionFactory(
            new ClassNameFactory(),
            StepMethodFactory::createFactory(),
            ArgumentFactory::createFactory()
        );
    }

    /**
     * @param null|non-empty-string $fullyQualifiedBaseClass
     *
     * @throws UnsupportedStepException
     */
    public function createClassDefinition(
        NamedTestInterface $test,
        ?string $fullyQualifiedBaseClass = null
    ): ClassDefinitionInterface {
        $methodDefinitions = [
            $this->createSetupBeforeClassMethod($test),
        ];

        $stepOrdinalIndex = 1;
        foreach ($test->getSteps() as $stepName => $step) {
            $methodDefinitions = array_merge(
                $methodDefinitions,
                $this->stepMethodFactory->create($stepOrdinalIndex, $stepName, $step)
            );
            ++$stepOrdinalIndex;
        }

        $baseClass = is_string($fullyQualifiedBaseClass) ? new ClassName($fullyQualifiedBaseClass) : null;

        return new ClassDefinition(
            new ClassSignature(
                $this->classNameFactory->create($test),
                $baseClass
            ),
            new ClassBody($methodDefinitions)
        );
    }

    private function createSetupBeforeClassMethod(TestInterface $test): MethodDefinitionInterface
    {
        $setupBeforeClassBody = new Body([
            new Statement(
                new ObjectMethodInvocation(
                    object: new StaticObject('self'),
                    methodName: 'setClientManager',
                    arguments: new MethodArguments(
                        [
                            new ObjectConstructor(
                                class: new ClassName(ClientManager::class),
                                arguments: new MethodArguments($this->argumentFactory->create($test->getBrowser())),
                                mightThrow: true,
                            ),
                        ]
                    ),
                    mightThrow: false,
                )
            ),
            new Statement(
                new ObjectMethodInvocation(
                    object: new StaticObject('parent'),
                    methodName: 'setUpBeforeClass',
                    arguments: new MethodArguments(),
                    mightThrow: true,
                )
            ),
            new Statement(
                new ObjectMethodInvocation(
                    object: new VariableDependency(VariableName::PANTHER_CLIENT->value),
                    methodName: 'request',
                    arguments: new MethodArguments(
                        $this->argumentFactory->create('GET', $test->getUrl())
                    ),
                    mightThrow: true,
                )
            ),
        ]);

        $method = new MethodDefinition('setUpBeforeClass', $setupBeforeClassBody);

        $method->setStatic();
        $method->setReturnType('void');

        return $method;
    }
}
