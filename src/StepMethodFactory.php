<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSource\Annotation\DataProviderAnnotation;
use webignition\BasilCompilableSource\Block\IfBlock\IfBlock;
use webignition\BasilCompilableSource\Body\Body;
use webignition\BasilCompilableSource\DataProviderMethodDefinition;
use webignition\BasilCompilableSource\DocBlock\DocBlock;
use webignition\BasilCompilableSource\EmptyLine;
use webignition\BasilCompilableSource\Expression\ArrayExpression;
use webignition\BasilCompilableSource\Expression\LiteralExpression;
use webignition\BasilCompilableSource\Expression\ReturnExpression;
use webignition\BasilCompilableSource\Factory\ArgumentFactory;
use webignition\BasilCompilableSource\MethodArguments\MethodArguments;
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
     * @throws UnsupportedStepException
     *
     * @return MethodDefinitionInterface[]
     */
    public function create(int $index, string $stepName, StepInterface $step): array
    {
        $dataSetCollection = $step->getData() ?? new DataSetCollection([]);
        $parameterNames = $dataSetCollection->getParameterNames();

        $testMethod = new MethodDefinition(
            'test' . (string) $index,
            new Body([
                $this->createIfHasExpressionBlock(),
                $this->createSetBasilStepNameStatement($stepName),
                $this->createSetCurrentDataSetStatement($parameterNames),
                new EmptyLine(),
                $this->stepHandler->handle($step),
            ]),
            $parameterNames
        );

        $hasDataProvider = count($parameterNames) > 0;
        if (false === $hasDataProvider) {
            return [$testMethod];
        }

        $dataProviderMethod = new DataProviderMethodDefinition(
            'dataProvider' . (string) $index,
            $this->createEscapedDataProviderData($dataSetCollection)
        );

        $testMethodDocBlock = $testMethod->getDocBlock();
        if ($testMethodDocBlock instanceof DocBlock) {
            $testMethodDocBlock = $testMethodDocBlock->prepend(new DocBlock([
                new DataProviderAnnotation($dataProviderMethod->getName()),
                "\n",
            ]));
            $testMethod = $testMethod->withDocBlock($testMethodDocBlock);
        }

        return [
            $testMethod,
            $dataProviderMethod,
        ];
    }

    /**
     * @return array<string, array<int|string, string>>
     */
    private function createEscapedDataProviderData(DataSetCollectionInterface $dataSetCollection): array
    {
        $parameterNames = $dataSetCollection->getParameterNames();
        $data = $dataSetCollection->toArray();

        foreach ($data as $index => $dataSet) {
            $data[$index] = $this->createPreparedDataSet($parameterNames, $dataSet);
        }

        return $data;
    }

    /**
     * @param string[]                  $parameterNames
     * @param array<int|string, string> $dataSet
     *
     * @return array<int|string, string>
     */
    private function createPreparedDataSet(array $parameterNames, array $dataSet): array
    {
        $preparedDataSet = [];

        foreach ($parameterNames as $parameterName) {
            $parameter = $dataSet[$parameterName] ?? '';
            $preparedDataSet[$parameterName] = $this->singleQuotedStringEscaper->escape($parameter);
        }

        return $preparedDataSet;
    }

    private function createSetBasilStepNameStatement(string $stepName): StatementInterface
    {
        return new Statement(
            new ObjectMethodInvocation(
                new VariableDependency(VariableNames::PHPUNIT_TEST_CASE),
                'setBasilStepName',
                new MethodArguments($this->argumentFactory->create($stepName))
            )
        );
    }

    private function createIfHasExpressionBlock(): IfBlock
    {
        $expression = new StaticObjectMethodInvocation(
            new StaticObject('self'),
            'hasException'
        );

        $body = new Body([
            new Statement(
                new ReturnExpression()
            )
        ]);

        return new IfBlock($expression, $body);
    }

    /**
     * @param string[] $parameterNames
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
                    new MethodArguments([
                        ArrayExpression::fromArray([
                            'name' => new ObjectMethodInvocation(
                                new VariableDependency(VariableNames::PHPUNIT_TEST_CASE),
                                'dataName'
                            ),
                            'data' => $dataSetData,
                        ])
                    ])
                )
            ];
        }

        return new Statement(
            new ObjectMethodInvocation(
                new VariableDependency(VariableNames::PHPUNIT_TEST_CASE),
                'setCurrentDataSet',
                new MethodArguments($arguments)
            )
        );
    }
}
