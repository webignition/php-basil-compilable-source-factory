<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStepException;
use webignition\BasilCompilableSourceFactory\Handler\Step\StepHandler;
use webignition\BasilCompilableSourceFactory\Model\Attribute\DataProviderAttribute;
use webignition\BasilCompilableSourceFactory\Model\Attribute\StepNameAttribute;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\DataProviderMethodDefinition;
use webignition\BasilCompilableSourceFactory\Model\MethodDefinition;
use webignition\BasilCompilableSourceFactory\Model\MethodDefinitionInterface;
use webignition\BasilModels\Model\DataSet\DataSetCollection;
use webignition\BasilModels\Model\DataSet\DataSetCollectionInterface;
use webignition\BasilModels\Model\Statement\Action\ActionInterface;
use webignition\BasilModels\Model\Statement\Assertion\AssertionInterface;
use webignition\BasilModels\Model\Statement\StatementCollection;
use webignition\BasilModels\Model\Step\StepInterface;

/**
 * @phpstan-import-type SerializedAction from ActionInterface
 * @phpstan-import-type SerializedAssertion from AssertionInterface
 */
class StepMethodFactory
{
    public function __construct(
        private StepHandler $stepHandler,
        private SingleQuotedStringEscaper $singleQuotedStringEscaper,
        private StatementsAttributeFactory $statementsAttributeFactory,
    ) {}

    public static function createFactory(): self
    {
        return new StepMethodFactory(
            StepHandler::createHandler(),
            SingleQuotedStringEscaper::create(),
            StatementsAttributeFactory::createFactory(),
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
            'test' . $index,
            new Body($this->stepHandler->handle($step)),
            $parameterNames
        );

        $testMethod = $testMethod->withAttribute(
            new StepNameAttribute($this->singleQuotedStringEscaper->escape($stepName))
        );

        $statements = new StatementCollection([])
            ->append($step->getActions())
            ->append($step->getAssertions())
        ;

        $testMethod = $testMethod->withAttribute(
            $this->statementsAttributeFactory->create($statements)
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
}
