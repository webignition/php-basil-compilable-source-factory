<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilableSourceFactory\Handler\Action\ActionHandler;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilationSource\Comment;
use webignition\BasilCompilationSource\EmptyLine;
use webignition\BasilCompilationSource\LineList;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\Statement;
use webignition\BasilModel\Step\StepInterface;

class StepHandler implements HandlerInterface
{
    private $actionHandler;

    public function __construct(HandlerInterface $actionHandler)
    {
        $this->actionHandler = $actionHandler;
    }

    public static function createHandler(): HandlerInterface
    {
        return new StepHandler(
            ActionHandler::createHandler()
        );
    }

    public function handles(object $model): bool
    {
        return $model instanceof StepInterface;
    }

    public function createSource(object $model): SourceInterface
    {
        if (!$model instanceof StepInterface) {
            throw new UnsupportedModelException($model);
        }

        $statementList = new LineList([]);

        $actions = $model->getActions();
        $actionCount = count($actions);
        $hasMoreThanOneAction = $actionCount > 1;

        foreach ($actions as $actionIndex => $action) {
            $actionSource = $this->actionHandler->createSource($action);

            $statementList->addLine(new Comment($action->getActionString()));
            $statementList->addLines($actionSource->getLineObjects());

            if ($hasMoreThanOneAction && $actionIndex < $actionCount -1) {
                $statementList->addLine(new EmptyLine());
            }
        }

        return $statementList;
    }
}
