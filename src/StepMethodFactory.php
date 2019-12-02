<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStepException;
use webignition\BasilCompilableSourceFactory\Handler\StepHandler;
use webignition\BasilCompilableSourceFactory\Model\StepMethods;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\DocBlock;
use webignition\BasilCompilationSource\Line\Comment;
use webignition\BasilCompilationSource\MethodDefinition\MethodDefinition;
use webignition\BasilCompilationSource\MethodDefinition\MethodDefinitionInterface;
use webignition\BasilModels\DataSet\DataSetCollectionInterface;
use webignition\BasilModels\Step\StepInterface;

class StepMethodFactory
{
    private $stepHandler;
    private $arrayStatementFactory;
    private $stepMethodNameFactory;

    public function __construct(
        StepHandler $stepHandler,
        ArrayStatementFactory $arrayStatementFactory,
        StepMethodNameFactory $stepMethodNameFactory
    ) {
        $this->stepHandler = $stepHandler;
        $this->arrayStatementFactory = $arrayStatementFactory;
        $this->stepMethodNameFactory = $stepMethodNameFactory;
    }

    public static function createFactory(): StepMethodFactory
    {
        return new StepMethodFactory(
            StepHandler::createHandler(),
            ArrayStatementFactory::createFactory(),
            new StepMethodNameFactory()
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
                new Comment($stepName),
                $this->stepHandler->handle($step),
            ]),
            $parameterNames
        );

        $dataProviderMethod = null;
        if ($dataSetCollection instanceof DataSetCollectionInterface && count($parameterNames) > 0) {
            $dataProviderMethod = $this->createDataProviderMethod($stepName, $dataSetCollection);

            $testMethod->setDocBlock(new DocBlock([
                new Comment('@dataProvider ' . $dataProviderMethod->getName()),
            ]));
        }

        return new StepMethods($testMethod, $dataProviderMethod);
    }

    private function createDataProviderMethod(
        string $stepName,
        DataSetCollectionInterface $dataSetCollection
    ): MethodDefinitionInterface {
        return new MethodDefinition(
            $this->stepMethodNameFactory->createDataProviderMethodName($stepName),
            new CodeBlock([
                $this->arrayStatementFactory->create($dataSetCollection),
            ])
        );
    }
}
