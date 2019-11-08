<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilableSourceFactory\Handler\Action\ActionHandler;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\AssertionHandler;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilationSource\Comment;
use webignition\BasilCompilationSource\EmptyLine;
use webignition\BasilCompilationSource\LineList;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilModel\StatementInterface;
use webignition\BasilModel\Step\StepInterface;

class StepHandler implements HandlerInterface
{
    private $actionHandler;
    private $assertionHandler;

    public function __construct(HandlerInterface $actionHandler, HandlerInterface $assertionHandler)
    {
        $this->actionHandler = $actionHandler;
        $this->assertionHandler = $assertionHandler;
    }

    public static function createHandler(): HandlerInterface
    {
        return new StepHandler(
            ActionHandler::createHandler(),
            AssertionHandler::createHandler()
        );
    }

    public function handles(object $model): bool
    {
        return $model instanceof StepInterface;
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
        if (!$model instanceof StepInterface) {
            throw new UnsupportedModelException($model);
        }

        $lineList = new LineList([]);

        foreach ($model->getActions() as $action) {
            $this->addSourceToLineList($lineList, $action, $this->actionHandler->handle($action));
        }

        foreach ($model->getAssertions() as $assertion) {
            $this->addSourceToLineList($lineList, $assertion, $this->assertionHandler->handle($assertion));
        }

        return $lineList;
    }

    private function addSourceToLineList(LineList $lineList, StatementInterface $statement, SourceInterface $source)
    {
        $lineList->addLinesFromSources([
            new Comment($statement->getSource()),
            $source,
            new EmptyLine(),
        ]);
    }
}
