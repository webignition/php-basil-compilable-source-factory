<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSourceFactory\Enum\VariableName as VariableNameEnum;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStepException;
use webignition\BasilCompilableSourceFactory\Handler\Step\StepHandler;
use webignition\BasilCompilableSourceFactory\Model\Attribute\DataProviderAttribute;
use webignition\BasilCompilableSourceFactory\Model\Attribute\StepNameAttribute;
use webignition\BasilCompilableSourceFactory\Model\Block\IfBlock\IfBlock;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\DataProviderMethodDefinition;
use webignition\BasilCompilableSourceFactory\Model\EmptyLine;
use webignition\BasilCompilableSourceFactory\Model\Expression\ArrayExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ReturnExpression;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodDefinition;
use webignition\BasilCompilableSourceFactory\Model\MethodDefinitionInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\StaticObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\Statement\StatementInterface;
use webignition\BasilCompilableSourceFactory\Model\StaticObject;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilCompilableSourceFactory\Model\VariableName;
use webignition\BasilModels\Model\DataSet\DataSet;
use webignition\BasilModels\Model\DataSet\DataSetCollection;
use webignition\BasilModels\Model\DataSet\DataSetCollectionInterface;
use webignition\BasilModels\Model\Step\StepInterface;

class StepMethodFactory
{
    public function __construct(
        private StepHandler $stepHandler,
        private SingleQuotedStringEscaper $singleQuotedStringEscaper,
    ) {}

    public static function createFactory(): self
    {
        return new StepMethodFactory(
            StepHandler::createHandler(),
            SingleQuotedStringEscaper::create(),
        );
    }

    /**
     * @return MethodDefinitionInterface[]
     *
     * @throws UnsupportedStepException
     */
    public function create(int $index, string $stepName, StepInterface $step): array
    {
        $dataSetCollection = $step->getData() ?? new DataSetCollection([]);
        $parameterNames = $dataSetCollection->getParameterNames();

        $testMethod = new MethodDefinition(
            'test' . (string) $index,
            new Body([
                $this->createIfHasExpressionBlock(),
                $this->createSetCurrentDataSetStatement($parameterNames),
                new EmptyLine(),
                $this->stepHandler->handle($step),
            ]),
            $parameterNames
        );

        $testMethod = $testMethod->withAttribute(
            new StepNameAttribute($this->singleQuotedStringEscaper->escape($stepName))
        );

        $hasDataProvider = count($parameterNames) > 0;
        if (false === $hasDataProvider) {
            return [$testMethod];
        }

        $dataProviderMethod = new DataProviderMethodDefinition(
            'dataProvider' . (string) $index,
            $this->createEscapedDataProviderData($dataSetCollection)
        );

        $testMethod = $testMethod->withAttribute(new DataProviderAttribute($dataProviderMethod->getName()));

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
                                new VariableDependency(VariableNameEnum::PHPUNIT_TEST_CASE),
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
                new VariableDependency(VariableNameEnum::PHPUNIT_TEST_CASE),
                'setCurrentDataSet',
                new MethodArguments($arguments)
            )
        );
    }
}
