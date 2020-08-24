<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSource\Body\Body;
use webignition\BasilCompilableSource\DataProvidedMethodDefinition;
use webignition\BasilCompilableSource\DataProviderMethodDefinition;
use webignition\BasilCompilableSource\EmptyLine;
use webignition\BasilCompilableSource\Expression\ArrayExpression;
use webignition\BasilCompilableSource\Expression\LiteralExpression;
use webignition\BasilCompilableSource\Factory\ArgumentFactory;
use webignition\BasilCompilableSource\MethodDefinition;
use webignition\BasilCompilableSource\MethodDefinitionInterface;
use webignition\BasilCompilableSource\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\MethodInvocation\StaticObjectMethodInvocation;
use webignition\BasilCompilableSource\Statement\Statement;
use webignition\BasilCompilableSource\Statement\StatementInterface;
use webignition\BasilCompilableSource\StaticObject;
use webignition\BasilCompilableSource\VariableDependency;
use webignition\BasilCompilableSource\VariableName;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStepException;
use webignition\BasilCompilableSourceFactory\Handler\Step\StepHandler;
use webignition\BasilModels\DataSet\DataSet;
use webignition\BasilModels\DataSet\DataSetCollection;
use webignition\BasilModels\DataSet\DataSetCollectionInterface;
use webignition\BasilModels\Step\StepInterface;

class StepMethodFactory
{
    private StepHandler $stepHandler;
    private SingleQuotedStringEscaper $singleQuotedStringEscaper;
    private ArgumentFactory $argumentFactory;

    public function __construct(
        StepHandler $stepHandler,
        SingleQuotedStringEscaper $singleQuotedStringEscaper,
        ArgumentFactory $argumentFactory
    ) {
        $this->stepHandler = $stepHandler;
        $this->singleQuotedStringEscaper = $singleQuotedStringEscaper;
        $this->argumentFactory = $argumentFactory;
    }

    public static function createFactory(): self
    {
        return new StepMethodFactory(
            StepHandler::createHandler(),
            SingleQuotedStringEscaper::create(),
            ArgumentFactory::createFactory()
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
        $dataSetCollection = $step->getData() ?? new DataSetCollection([]);
        $parameterNames = $dataSetCollection->getParameterNames();

        $testMethod = new MethodDefinition(
            'test' . (string) $index,
            new Body([
                $this->createSetBasilStepNameStatement($stepName),
                $this->createSetCurrentDataSetStatement($parameterNames),
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

    private function createSetBasilStepNameStatement(string $stepName): StatementInterface
    {
        return new Statement(
            new ObjectMethodInvocation(
                new VariableDependency(VariableNames::PHPUNIT_TEST_CASE),
                'setBasilStepName',
                $this->argumentFactory->create($stepName)
            )
        );
    }

    /**
     * @param string[] $parameterNames
     *
     * @return StatementInterface
     */
    private function createSetCurrentDataSetStatement(array $parameterNames): StatementInterface
    {
        $arguments = [
            new LiteralExpression('null'),
        ];

        if (0 !== count($parameterNames)) {
            $dataSetData = [];
            foreach ($parameterNames as $parameterName) {
                $dataSetData[$parameterName] = new VariableName($parameterName);
            }

            $arguments = [
                new StaticObjectMethodInvocation(
                    new StaticObject(DataSet::class),
                    'fromArray',
                    [
                        new ArrayExpression([
                            'name' => new ObjectMethodInvocation(
                                new VariableDependency(VariableNames::PHPUNIT_TEST_CASE),
                                'dataName'
                            ),
                            'data' => $dataSetData,
                        ])
                    ]
                )
            ];
        }

        return new Statement(
            new ObjectMethodInvocation(
                new VariableDependency(VariableNames::PHPUNIT_TEST_CASE),
                'setCurrentDataSet',
                $arguments
            )
        );
    }
}
