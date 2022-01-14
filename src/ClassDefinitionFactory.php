<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BaseBasilTestCase\ClientManager;
use webignition\BasilCompilableSource\Block\TryCatch\CatchBlock;
use webignition\BasilCompilableSource\Block\TryCatch\TryBlock;
use webignition\BasilCompilableSource\Block\TryCatch\TryCatchBlock;
use webignition\BasilCompilableSource\Body\Body;
use webignition\BasilCompilableSource\ClassBody;
use webignition\BasilCompilableSource\ClassDefinition;
use webignition\BasilCompilableSource\ClassDefinitionInterface;
use webignition\BasilCompilableSource\ClassName;
use webignition\BasilCompilableSource\ClassSignature;
use webignition\BasilCompilableSource\Expression\CatchExpression;
use webignition\BasilCompilableSource\Factory\ArgumentFactory;
use webignition\BasilCompilableSource\MethodArguments\MethodArguments;
use webignition\BasilCompilableSource\MethodDefinition;
use webignition\BasilCompilableSource\MethodDefinitionInterface;
use webignition\BasilCompilableSource\MethodInvocation\ObjectConstructor;
use webignition\BasilCompilableSource\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\MethodInvocation\StaticObjectMethodInvocation;
use webignition\BasilCompilableSource\Statement\Statement;
use webignition\BasilCompilableSource\StaticObject;
use webignition\BasilCompilableSource\TypeDeclaration\ObjectTypeDeclaration;
use webignition\BasilCompilableSource\TypeDeclaration\ObjectTypeDeclarationCollection;
use webignition\BasilCompilableSource\VariableDependency;
use webignition\BasilCompilableSource\VariableName;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStepException;
use webignition\BasilModels\Step\StepInterface;
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
     * @throws UnsupportedStepException
     */
    public function createClassDefinition(
        TestInterface $test,
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
        $testConfiguration = $test->getConfiguration();

        $tryBody = new Body([
            new Statement(
                new StaticObjectMethodInvocation(
                    new StaticObject('self'),
                    'setClientManager',
                    new MethodArguments(
                        [
                            new ObjectConstructor(
                                new ClassName(ClientManager::class),
                                new MethodArguments(
                                    [
                                        new ObjectConstructor(
                                            new ClassName(Configuration::class),
                                            new MethodArguments(
                                                $this->argumentFactory->create(
                                                    $testConfiguration->getBrowser(),
                                                    $testConfiguration->getUrl()
                                                ),
                                                MethodArguments::FORMAT_STACKED
                                            )
                                        ),
                                    ],
                                    MethodArguments::FORMAT_STACKED
                                )
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
                        $this->argumentFactory->create('GET', $testConfiguration->getUrl())
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
