<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilableSourceFactory\Handler\Action\ActionHandler;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\AssertionHandler;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilationSource\Block\Block;
use webignition\BasilCompilationSource\Block\BlockInterface;
use webignition\BasilCompilationSource\Line\Comment;
use webignition\BasilCompilationSource\Line\EmptyLine;
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

        $block = new Block([]);

        foreach ($model->getActions() as $action) {
            $this->addSourceToLineList($block, $action, $this->actionHandler->handle($action));
        }

        foreach ($model->getAssertions() as $assertion) {
            $this->addSourceToLineList($block, $assertion, $this->assertionHandler->handle($assertion));
        }

        return $block;
    }

    private function addSourceToLineList(Block $block, StatementInterface $statement, SourceInterface $source)
    {
        $block->addLine(new Comment($statement->getSource()));

        if ($source instanceof BlockInterface) {
            foreach ($source->getLines() as $sourceLine) {
                $block->addLine($sourceLine);
            }
        }

        $block->addLine(new EmptyLine());
    }
}
