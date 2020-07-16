<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BaseBasilTestCase\AbstractBaseTest;
use webignition\BasilCompilableSource\Body\Body;
use webignition\BasilCompilableSource\ClassDefinition;
use webignition\BasilCompilableSource\ClassDefinitionInterface;
use webignition\BasilCompilableSource\Expression\LiteralExpression;
use webignition\BasilCompilableSource\MethodDefinition;
use webignition\BasilCompilableSource\MethodDefinitionInterface;
use webignition\BasilCompilableSource\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\MethodInvocation\StaticObjectMethodInvocation;
use webignition\BasilCompilableSource\Statement\Statement;
use webignition\BasilCompilableSource\StaticObject;
use webignition\BasilCompilableSource\VariableDependency;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStepException;
use webignition\BasilModels\Test\TestInterface;

class ClassDefinitionFactory
{
    private ClassNameFactory $classNameFactory;
    private StepMethodFactory $stepMethodFactory;

    private const DEFAULT_CLIENT_ID = AbstractBaseTest::BROWSER_CHROME;
    private const BROWSER_NAME_CLIENT_ID_MAP = [
        'chrome' => AbstractBaseTest::BROWSER_CHROME,
        'firefox' => AbstractBaseTest::BROWSER_FIREFOX,
    ];

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

        $browser = $testConfiguration->getBrowser();
        $browserClientId = self::BROWSER_NAME_CLIENT_ID_MAP[$browser] ?? self::DEFAULT_CLIENT_ID;

        $method = new MethodDefinition('setUpBeforeClass', new Body([
            new Statement(
                new StaticObjectMethodInvocation(
                    new StaticObject('self'),
                    'setUpClient',
                    [
                        new LiteralExpression((string) $browserClientId),
                    ]
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
                    [
                        new LiteralExpression('\'GET\''),
                        new LiteralExpression('\'' . $testConfiguration->getUrl() . '\''),
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
