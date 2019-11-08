<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\SingleQuotedStringEscaper;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\ClassDefinition;
use webignition\BasilCompilationSource\Comment;
use webignition\BasilCompilationSource\LineList;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\MethodDefinition;
use webignition\BasilCompilationSource\MethodDefinitionInterface;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\Statement;
use webignition\BasilCompilationSource\StatementInterface;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Test\TestInterface;

class TestHandler implements HandlerInterface
{
    private $stepHandler;
    private $singleQuotedStringEscaper;

    public function __construct(HandlerInterface $stepHandler, SingleQuotedStringEscaper $singleQuotedStringEscaper)
    {
        $this->stepHandler = $stepHandler;
        $this->singleQuotedStringEscaper = $singleQuotedStringEscaper;
    }

    public static function createHandler(): HandlerInterface
    {
        return new TestHandler(
            StepHandler::createHandler(),
            SingleQuotedStringEscaper::create()
        );
    }

    public function handles(object $model): bool
    {
        return $model instanceof TestInterface;
    }

    /**
     * @param object $model
     *
     * @return SourceInterface
     *
     * @throws UnsupportedModelException
     */
    public function handle(object $model): SourceInterface
    {
        if (!$model instanceof TestInterface) {
            throw new UnsupportedModelException($model);
        }

        $methodDefinitions = [
            $this->createSetupBeforeClassMethod($model),
        ];

        foreach ($model->getSteps() as $stepName => $step) {
            $stepName = (string) $stepName;

            $stepMethodName = sprintf('test%s', ucfirst(md5($stepName)));

            $methodDefinitions[] = new MethodDefinition(
                $stepMethodName,
                new LineList([
                    new Comment($stepName),
                    $this->stepHandler->handle($step),
                ])
            );
        }

        $testName = (string) $model->getName();
        $className = sprintf('Generated%sTest', ucfirst(md5($testName)));

        return new ClassDefinition($className, $methodDefinitions);
    }

    private function createSetupBeforeClassMethod(TestInterface $test): MethodDefinitionInterface
    {
        $parentCallStatement = new Statement('parent::setUpBeforeClass()');
        $clientRequestStatement = $this->createClientRequestStatement($test);

        $setupBeforeClassMethod = new MethodDefinition('setUpBeforeClass', new LineList([
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
}
