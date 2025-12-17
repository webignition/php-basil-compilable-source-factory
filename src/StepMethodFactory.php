<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStepException;
use webignition\BasilCompilableSourceFactory\Handler\Step\StepHandler;
use webignition\BasilCompilableSourceFactory\Model\Attribute\DataProviderAttribute;
use webignition\BasilCompilableSourceFactory\Model\Attribute\StatementsAttribute;
use webignition\BasilCompilableSourceFactory\Model\Attribute\StepNameAttribute;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\DataProviderMethodDefinition;
use webignition\BasilCompilableSourceFactory\Model\MethodDefinition;
use webignition\BasilCompilableSourceFactory\Model\MethodDefinitionInterface;
use webignition\BasilModels\Model\DataSet\DataSetCollection;
use webignition\BasilModels\Model\DataSet\DataSetCollectionInterface;
use webignition\BasilModels\Model\Step\StepInterface;

class StepMethodFactory
{
    public function __construct(
        private StepHandler $stepHandler,
        private SingleQuotedStringEscaper $singleQuotedStringEscaper,
        private StatementsAttributeValuePrinter $statementsAttributeValuePrinter,
    ) {}

    public static function createFactory(): self
    {
        return new StepMethodFactory(
            StepHandler::createHandler(),
            SingleQuotedStringEscaper::create(),
            StatementsAttributeValuePrinter::create(),
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
                $this->stepHandler->handle($step),
            ]),
            $parameterNames
        );

        $testMethod->setReturnType('void');

        $testMethod = $testMethod->withAttribute(
            new StepNameAttribute($this->singleQuotedStringEscaper->escape($stepName))
        );

        $testMethod = $testMethod->withAttribute(
            new StatementsAttribute(
                $this->statementsAttributeValuePrinter->print($this->buildStepStatementsAttributeContent($step))
            )
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

    /**
     * @return array{'type':'action'|'assertion', 'statement':non-empty-string}[]
     */
    private function buildStepStatementsAttributeContent(StepInterface $step): array
    {
        $statements = [];

        foreach ($step->getActions() as $action) {
            $source = $action->getSource();
            \assert('' !== $source);

            $statements[] = [
                'type' => 'action',
                'statement' => $source,
            ];
        }

        foreach ($step->getAssertions() as $assertion) {
            $source = $assertion->getSource();
            \assert('' !== $source);

            $statements[] = [
                'type' => 'assertion',
                'statement' => $source,
            ];
        }

        return $statements;
    }
}
