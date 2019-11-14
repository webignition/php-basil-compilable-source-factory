<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler;

use webignition\BasilCompilableSourceFactory\ArrayStatementFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnknownObjectPropertyException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilableSourceFactory\SingleQuotedStringEscaper;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\DocBlock;
use webignition\BasilCompilationSource\ClassDefinition\ClassDefinition;
use webignition\BasilCompilationSource\ClassDefinition\ClassDefinitionInterface;
use webignition\BasilCompilationSource\Line\Comment;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\Line\StatementInterface;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\BasilCompilationSource\MethodDefinition\MethodDefinition;
use webignition\BasilCompilationSource\MethodDefinition\MethodDefinitionInterface;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\DataSet\DataSetCollectionInterface;
use webignition\BasilModel\Test\TestInterface;

class TestHandler
{
    private $stepHandler;
    private $singleQuotedStringEscaper;
    private $arrayStatementFactory;

    public function __construct(
        StepHandler $stepHandler,
        SingleQuotedStringEscaper $singleQuotedStringEscaper,
        ArrayStatementFactory $arrayStatementFactory
    ) {
        $this->stepHandler = $stepHandler;
        $this->singleQuotedStringEscaper = $singleQuotedStringEscaper;
        $this->arrayStatementFactory = $arrayStatementFactory;
    }

    public static function createHandler(): TestHandler
    {
        return new TestHandler(
            StepHandler::createHandler(),
            SingleQuotedStringEscaper::create(),
            ArrayStatementFactory::createFactory()
        );
    }

    public function handles(object $model): bool
    {
        return $model instanceof TestInterface;
    }

    /**
     * @param object $model
     *
     * @return ClassDefinitionInterface
     *
     * @throws UnsupportedModelException
     * @throws UnknownObjectPropertyException
     */
    public function handle(object $model): ClassDefinitionInterface
    {
        if (!$model instanceof TestInterface) {
            throw new UnsupportedModelException($model);
        }

        $methodDefinitions = [
            $this->createSetupBeforeClassMethod($model),
        ];

        foreach ($model->getSteps() as $stepName => $step) {
            $stepName = (string) $stepName;
            $dataSetCollection = $step->getDataSetCollection();
            $parameterNames = $dataSetCollection->getParameterNames();

            $stepMethodIdentifier = ucfirst(md5($stepName));
            $stepMethodName = sprintf('test%s', $stepMethodIdentifier);

            $stepMethod = new MethodDefinition(
                $stepMethodName,
                new CodeBlock([
                    new Comment($stepName),
                    $this->stepHandler->handle($step),
                ]),
                $parameterNames
            );

            $hasData = count($parameterNames) > 0;

            if ($hasData) {
                $dataProviderMethod = $this->createDataProviderMethod($stepMethodIdentifier, $dataSetCollection);

                $stepMethod->setDocBlock(new DocBlock([
                    new Comment('@dataProvider ' . $dataProviderMethod->getName()),
                ]));

                $methodDefinitions[] = $stepMethod;
                $methodDefinitions[] = $dataProviderMethod;
            } else {
                $methodDefinitions[] = $stepMethod;
            }
        }

        $testName = (string) $model->getName();
        $className = sprintf('Generated%sTest', ucfirst(md5($testName)));

        return new ClassDefinition($className, $methodDefinitions);
    }

    private function createSetupBeforeClassMethod(TestInterface $test): MethodDefinitionInterface
    {
        $parentCallStatement = new Statement('parent::setUpBeforeClass()');
        $clientRequestStatement = $this->createClientRequestStatement($test);

        $setupBeforeClassMethod = new MethodDefinition('setUpBeforeClass', new CodeBlock([
            $parentCallStatement,
            $clientRequestStatement,
        ]));

        $setupBeforeClassMethod->setStatic();
        $setupBeforeClassMethod->setReturnType('void');

        return $setupBeforeClassMethod;
    }

    private function createClientRequestStatement(TestInterface $test): StatementInterface
    {
        $variableDependencies = new VariablePlaceholderCollection();
        $pantherClientPlaceholder = $variableDependencies->create(VariableNames::PANTHER_CLIENT);

        $clientRequestStatement = new Statement(
            sprintf(
                '%s->request(\'GET\', \'%s\')',
                $pantherClientPlaceholder,
                $test->getConfiguration()->getUrl()
            ),
            (new Metadata())
                ->withVariableDependencies($variableDependencies)
        );

        return $clientRequestStatement;
    }

    private function createDataProviderMethod(
        string $stepMethodIdentifier,
        DataSetCollectionInterface $dataSetCollection
    ): MethodDefinitionInterface {
        return new MethodDefinition($stepMethodIdentifier . 'DataProvider', new CodeBlock([
            $this->arrayStatementFactory->create($dataSetCollection),
        ]));
    }
}
