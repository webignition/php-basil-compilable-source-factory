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

    public function createSource(object $model): SourceInterface
    {
        if (!$model instanceof TestInterface) {
            throw new UnsupportedModelException($model);
        }

        $methodDefinitions = [
            $this->createSetupMethod($model),
            $this->createOpenMethod($model),
        ];

        foreach ($model->getSteps() as $stepName => $step) {
            $stepName = (string) $stepName;

            $stepMethodName = sprintf('test%s', ucfirst(md5($stepName)));

            $methodDefinitions[] = new MethodDefinition(
                $stepMethodName,
                new LineList([
                    new Comment($stepName),
                    $this->stepHandler->createSource($step),
                ])
            );
        }

        $testName = (string) $model->getName();
        $className = sprintf('generated%sTest', ucfirst(md5($testName)));

        return new ClassDefinition($className, $methodDefinitions);
    }

    private function createSetupMethod(TestInterface $test): MethodDefinitionInterface
    {
        $escapedTestName = $this->singleQuotedStringEscaper->escape($test->getName());

        $setNameStatement = new Statement(sprintf('$this->setName(\'%s\')', $escapedTestName));

        $setupMethod = new MethodDefinition('setUp', new LineList([$setNameStatement]));
        $setupMethod->setProtected();

        return $setupMethod;
    }

    private function createOpenMethod(TestInterface $test): MethodDefinitionInterface
    {
        $requestStatementVariableDependencies = new VariablePlaceholderCollection();
        $pantherClientPlaceholder = $requestStatementVariableDependencies->create(VariableNames::PANTHER_CLIENT);

        $configuration = $test->getConfiguration();

        $requestStatement = new Statement(
            sprintf(
                '%s->request(\'GET\', \'%s\')',
                (string) $pantherClientPlaceholder,
                $configuration->getUrl()
            ),
            (new Metadata())
                ->withVariableDependencies($requestStatementVariableDependencies)
        );

        return new MethodDefinition('testOpen', new LineList([$requestStatement]));
    }
}
