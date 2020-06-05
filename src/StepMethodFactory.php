<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSource\Block\CodeBlock;
use webignition\BasilCompilableSource\Block\DocBlock;
use webignition\BasilCompilableSource\Line\EmptyLine;
use webignition\BasilCompilableSource\Line\Literal;
use webignition\BasilCompilableSource\Line\LiteralExpression;
use webignition\BasilCompilableSource\Line\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\Line\Statement\ReturnStatement;
use webignition\BasilCompilableSource\Line\Statement\Statement;
use webignition\BasilCompilableSource\MethodDefinition;
use webignition\BasilCompilableSource\VariableDependency;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStepException;
use webignition\BasilCompilableSourceFactory\Handler\Step\StepHandler;
use webignition\BasilCompilableSourceFactory\Model\StepMethods;
use webignition\BasilModels\DataSet\DataSetCollectionInterface;
use webignition\BasilModels\Step\StepInterface;

class StepMethodFactory
{
    private StepHandler $stepHandler;
    private ArrayExpressionFactory $arrayExpressionFactory;
    private StepMethodNameFactory $stepMethodNameFactory;
    private SingleQuotedStringEscaper $singleQuotedStringEscaper;

    public function __construct(
        StepHandler $stepHandler,
        ArrayExpressionFactory $arrayExpressionFactory,
        StepMethodNameFactory $stepMethodNameFactory,
        SingleQuotedStringEscaper $singleQuotedStringEscaper
    ) {
        $this->stepHandler = $stepHandler;
        $this->arrayExpressionFactory = $arrayExpressionFactory;
        $this->stepMethodNameFactory = $stepMethodNameFactory;
        $this->singleQuotedStringEscaper = $singleQuotedStringEscaper;
    }

    public static function createFactory(): StepMethodFactory
    {
        return new StepMethodFactory(
            StepHandler::createHandler(),
            ArrayExpressionFactory::createFactory(),
            new StepMethodNameFactory(),
            SingleQuotedStringEscaper::create()
        );
    }

    /**
     * @param string $stepName
     * @param StepInterface $step
     *
     * @return StepMethods
     *
     * @throws UnsupportedStepException
     */
    public function createStepMethods(string $stepName, StepInterface $step): StepMethods
    {
        $dataSetCollection = $step->getData();
        $parameterNames = [];

        if ($dataSetCollection instanceof DataSetCollectionInterface) {
            $parameterNames = $dataSetCollection->getParameterNames();
        }

        $stepMethodName = $this->stepMethodNameFactory->createTestMethodName($stepName);

        $testMethod = new MethodDefinition(
            $stepMethodName,
            new CodeBlock([
                new Statement(
                    new ObjectMethodInvocation(
                        new VariableDependency(VariableNames::PHPUNIT_TEST_CASE),
                        'setBasilStepName',
                        [
                            new LiteralExpression('\'' . $this->singleQuotedStringEscaper->escape($stepName) . '\''),
                        ]
                    )
                ),
                new EmptyLine(),
                $this->stepHandler->handle($step),
            ]),
            $parameterNames
        );

        $dataProviderMethod = null;
        if ($dataSetCollection instanceof DataSetCollectionInterface && count($parameterNames) > 0) {
            $dataProviderMethod = new MethodDefinition(
                $this->stepMethodNameFactory->createDataProviderMethodName($stepName),
                new CodeBlock([
                    new ReturnStatement($this->arrayExpressionFactory->create($dataSetCollection)),
                ])
            );

            $testMethod->setDocBlock(new DocBlock([
                new Literal('@dataProvider ' . $dataProviderMethod->getName()),
            ]));
        }

        return new StepMethods($testMethod, $dataProviderMethod);
    }
}
