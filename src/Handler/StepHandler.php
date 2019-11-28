<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler;

use webignition\BasilCompilableSourceFactory\Exception\UnknownObjectPropertyException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilableSourceFactory\Handler\Action\ActionHandler;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\AssertionHandler;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\Line\Comment;
use webignition\BasilCompilationSource\Line\EmptyLine;
use webignition\BasilModel\StatementInterface;
use webignition\BasilModel\Step\StepInterface;

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
     * @throws UnsupportedModelException
     * @throws UnknownObjectPropertyException
     */
    public function handle(StepInterface $step): CodeBlockInterface
    {
        $block = new CodeBlock([]);

        foreach ($step->getActions() as $action) {
            $this->addSourceToBlock($block, $action, $this->actionHandler->handle($action));
        }

        foreach ($step->getAssertions() as $assertion) {
            $this->addSourceToBlock($block, $assertion, $this->assertionHandler->handle($assertion));
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
