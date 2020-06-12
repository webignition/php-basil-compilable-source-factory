<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSource\Body\Body;
use webignition\BasilCompilableSource\DataProvidedMethodDefinition;
use webignition\BasilCompilableSource\DataProviderMethodDefinition;
use webignition\BasilCompilableSource\EmptyLine;
use webignition\BasilCompilableSource\Expression\LiteralExpression;
use webignition\BasilCompilableSource\MethodDefinitionInterface;
use webignition\BasilCompilableSource\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\Statement\Statement;
use webignition\BasilCompilableSource\MethodDefinition;
use webignition\BasilCompilableSource\VariableDependency;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStepException;
use webignition\BasilCompilableSourceFactory\Handler\Step\StepHandler;
use webignition\BasilModels\DataSet\DataSetCollectionInterface;
use webignition\BasilModels\Step\StepInterface;

class StepMethodFactory
{
    private StepHandler $stepHandler;
    private SingleQuotedStringEscaper $singleQuotedStringEscaper;

    public function __construct(StepHandler $stepHandler, SingleQuotedStringEscaper $singleQuotedStringEscaper)
    {
        $this->stepHandler = $stepHandler;
        $this->singleQuotedStringEscaper = $singleQuotedStringEscaper;
    }

    public static function createFactory(): self
    {
        return new StepMethodFactory(
            StepHandler::createHandler(),
            SingleQuotedStringEscaper::create()
        );
    }

    /**
     * @param int $index
     * @param string $stepName
     * @param StepInterface $step
     *
     * @return MethodDefinitionInterface
     *
     * @throws UnsupportedStepException
     */
    public function create(int $index, string $stepName, StepInterface $step): MethodDefinitionInterface
    {
        $dataSetCollection = $step->getData();
        $parameterNames = [];

        if ($dataSetCollection instanceof DataSetCollectionInterface) {
            $parameterNames = $dataSetCollection->getParameterNames();
        }

        $testMethod = new MethodDefinition(
            'test' . (string) $index,
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
                'dataProvider' . (string) $index,
                $this->createEscapedDataProviderData($dataSetCollection)
            );

            $testMethod = new DataProvidedMethodDefinition($testMethod, $dataProviderMethod);
        }

        return $testMethod;
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
