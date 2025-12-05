<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BaseBasilTestCase\ClientManager;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStepException;
use webignition\BasilCompilableSourceFactory\Model\Block\TryCatch\CatchBlock;
use webignition\BasilCompilableSourceFactory\Model\Block\TryCatch\TryBlock;
use webignition\BasilCompilableSourceFactory\Model\Block\TryCatch\TryCatchBlock;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\ClassBody;
use webignition\BasilCompilableSourceFactory\Model\ClassDefinition;
use webignition\BasilCompilableSourceFactory\Model\ClassDefinitionInterface;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\ClassSignature;
use webignition\BasilCompilableSourceFactory\Model\Expression\CatchExpression;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodDefinition;
use webignition\BasilCompilableSourceFactory\Model\MethodDefinitionInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectConstructor;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\StaticObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\StaticObject;
use webignition\BasilCompilableSourceFactory\Model\TypeDeclaration\ObjectTypeDeclaration;
use webignition\BasilCompilableSourceFactory\Model\TypeDeclaration\ObjectTypeDeclarationCollection;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilCompilableSourceFactory\Model\VariableName;
use webignition\BasilModels\Model\Step\StepInterface;
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
            if ($step instanceof StepInterface) {
                $methodDefinitions = array_merge(
                    $methodDefinitions,
                    $this->stepMethodFactory->create($stepOrdinalIndex, $stepName, $step)
                );
                ++$stepOrdinalIndex;
            }
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
        $tryBody = new Body([
            new Statement(
                new StaticObjectMethodInvocation(
                    new StaticObject('self'),
                    'setClientManager',
                    new MethodArguments(
                        [
                            new ObjectConstructor(
                                new ClassName(ClientManager::class),
                                new MethodArguments($this->argumentFactory->create($test->getBrowser()))
                            ),
                        ]
                    ),
                )
            ),
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
                    new MethodArguments(
                        $this->argumentFactory->create('GET', $test->getUrl())
                    )
                )
            ),
        ]);

        $catchBody = new Body([
            new Statement(
                new StaticObjectMethodInvocation(
                    new StaticObject('self'),
                    'staticSetLastException',
                    new MethodArguments([
                        new VariableName('exception')
                    ])
                ),
            ),
        ]);

        $tryCatchBlock = new TryCatchBlock(
            new TryBlock($tryBody),
            new CatchBlock(
                new CatchExpression(
                    new ObjectTypeDeclarationCollection([
                        new ObjectTypeDeclaration(new ClassName(\Throwable::class))
                    ])
                ),
                $catchBody
            )
        );

        $method = new MethodDefinition('setUpBeforeClass', $tryCatchBlock);

        $method->setStatic();
        $method->setReturnType('void');

        return $method;
    }
}
