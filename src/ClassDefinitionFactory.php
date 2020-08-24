<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSource\Body\Body;
use webignition\BasilCompilableSource\ClassDefinition;
use webignition\BasilCompilableSource\ClassDefinitionInterface;
use webignition\BasilCompilableSource\ClassName;
use webignition\BasilCompilableSource\Expression\LiteralExpression;
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
    private SingleQuotedStringEscaper $singleQuotedStringEscaper;

    public function __construct(
        ClassNameFactory $classNameFactory,
        StepMethodFactory $stepMethodFactory,
        SingleQuotedStringEscaper $singleQuotedStringEscaper
    ) {
        $this->classNameFactory = $classNameFactory;
        $this->stepMethodFactory = $stepMethodFactory;
        $this->singleQuotedStringEscaper = $singleQuotedStringEscaper;
    }

    public static function createFactory(): ClassDefinitionFactory
    {
        return new ClassDefinitionFactory(
            new ClassNameFactory(),
            StepMethodFactory::createFactory(),
            SingleQuotedStringEscaper::create()
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

        $escapedBrowser = $this->singleQuotedStringEscaper->escape($testConfiguration->getBrowser());
        $escapedUrl = $this->singleQuotedStringEscaper->escape($testConfiguration->getUrl());

        $method = new MethodDefinition('setUpBeforeClass', Body::createFromExpressions([
            new StaticObjectMethodInvocation(
                new StaticObject('self'),
                'setBasilTestConfiguration',
                [
                    (new ObjectConstructor(
                        new ClassName(Configuration::class),
                        [
                            new LiteralExpression('\'' . $escapedBrowser . '\''),
                            new LiteralExpression('\'' . $escapedUrl . '\''),
                        ],
                    ))->withStackedArguments(),
                ],
            ),
            new StaticObjectMethodInvocation(new StaticObject('parent'), 'setUpBeforeClass'),
            new ObjectMethodInvocation(
                new VariableDependency(VariableNames::PANTHER_CLIENT),
                'request',
                [
                    new LiteralExpression('\'GET\''),
                    new LiteralExpression('\'' . $escapedUrl . '\''),
                ]
            ),
            new StaticObjectMethodInvocation(
                new StaticObject('self'),
                'setBasilTestPath',
                [
                    new LiteralExpression('\'' . $test->getPath() . '\''),
                ]
            )
        ]));

        $method->setStatic();
        $method->setReturnType('void');

        return $method;
    }
}
