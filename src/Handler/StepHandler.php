<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedActionException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedAssertionException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStepException;
use webignition\BasilCompilableSourceFactory\Handler\Action\ActionHandler;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\AssertionHandler;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\Line\Comment;
use webignition\BasilCompilationSource\Line\EmptyLine;
use webignition\BasilModels\StatementInterface;
use webignition\BasilModels\Step\StepInterface;

class StepHandler
{
    private $actionHandler;
    private $assertionHandler;

    public function __construct(ActionHandler $actionHandler, AssertionHandler $assertionHandler)
    {
        $this->actionHandler = $actionHandler;
        $this->assertionHandler = $assertionHandler;
    }

    public static function createHandler(): StepHandler
    {
        return new StepHandler(
            ActionHandler::createHandler(),
            AssertionHandler::createHandler()
        );
    }

    /**
     * @param StepInterface $step
     *
     * @return CodeBlockInterface
     *
     * @throws UnsupportedStepException
     */
    public function handle(StepInterface $step): CodeBlockInterface
    {
        $block = new CodeBlock([]);

        try {
            foreach ($step->getActions() as $action) {
                $this->addSourceToBlock($block, $action, $this->actionHandler->handle($action));
            }

            foreach ($step->getAssertions() as $assertion) {
                $this->addSourceToBlock($block, $assertion, $this->assertionHandler->handle($assertion));
            }
        } catch (UnsupportedActionException | UnsupportedAssertionException $previous) {
            throw new UnsupportedStepException($step, $previous);
        }

        return $block;
    }

    private function addSourceToBlock(
        CodeBlockInterface $block,
        StatementInterface $statement,
        CodeBlockInterface $source
    ) {
        $block->addLine(new Comment($statement->getSource()));

        if ($source instanceof CodeBlockInterface) {
            foreach ($source->getLines() as $sourceLine) {
                $block->addLine($sourceLine);
            }
        }

        $block->addLine(new EmptyLine());
    }
}
