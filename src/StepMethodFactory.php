<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSource\Block\DocBlock;
use webignition\BasilCompilableSource\Body\Body;
use webignition\BasilCompilableSource\DataProviderMethodDefinition;
use webignition\BasilCompilableSource\EmptyLine;
use webignition\BasilCompilableSource\Expression\LiteralExpression;
use webignition\BasilCompilableSource\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\Statement\Statement;
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
    private StepMethodNameFactory $stepMethodNameFactory;
    private SingleQuotedStringEscaper $singleQuotedStringEscaper;

    public function __construct(
        StepHandler $stepHandler,
        StepMethodNameFactory $stepMethodNameFactory,
        SingleQuotedStringEscaper $singleQuotedStringEscaper
    ) {
        $this->stepHandler = $stepHandler;
        $this->stepMethodNameFactory = $stepMethodNameFactory;
        $this->singleQuotedStringEscaper = $singleQuotedStringEscaper;
    }

    public static function createFactory(): self
    {
        return new StepMethodFactory(
            StepHandler::createHandler(),
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
            new Body([
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
            $dataProviderMethod = new DataProviderMethodDefinition(
                $this->stepMethodNameFactory->createDataProviderMethodName($stepName),
                $this->createEscapedDataProviderData($dataSetCollection)
            );

            $testMethod->setDocBlock(new DocBlock([
                '@dataProvider ' . $dataProviderMethod->getName(),
            ]));
        }

        return new StepMethods($testMethod, $dataProviderMethod);
    }

    /**
     * @param DataSetCollectionInterface $dataSetCollection
     *
     * @return array<string, array<int|string, string>>
     */
    private function createEscapedDataProviderData(DataSetCollectionInterface $dataSetCollection): array
    {
        $data = $dataSetCollection->toArray();

        foreach ($data as $index => $dataSet) {
            foreach ($dataSet as $key => $value) {
                $dataSet[$key] = $this->singleQuotedStringEscaper->escape($value);
            }

            $data[$index] = $dataSet;
        }

        return $data;
    }
}
