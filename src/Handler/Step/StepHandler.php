<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Step;

use webignition\BasilCompilableSource\Block\CodeBlock;
use webignition\BasilCompilableSource\Block\CodeBlockInterface;
use webignition\BasilCompilableSource\Line\EmptyLine;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStatementException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStepException;
use webignition\BasilCompilableSourceFactory\Handler\Action\ActionHandler;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\AssertionHandler;
use webignition\BasilModels\Step\StepInterface;

class StepHandler
{
    private $actionHandler;
    private $assertionHandler;
    private $statementBlockFactory;
    private $fooDerivedAssertionFactory;

    public function __construct(
        ActionHandler $actionHandler,
        AssertionHandler $assertionHandler,
        StatementBlockFactory $statementBlockFactory,
        FooDerivedAssertionFactory $fooDerivedAssertionFactory
    ) {
        $this->actionHandler = $actionHandler;
        $this->assertionHandler = $assertionHandler;
        $this->statementBlockFactory = $statementBlockFactory;
        $this->fooDerivedAssertionFactory = $fooDerivedAssertionFactory;
    }

    public static function createHandler(): StepHandler
    {
        return new StepHandler(
            ActionHandler::createHandler(),
            AssertionHandler::createHandler(),
            StatementBlockFactory::createFactory(),
            FooDerivedAssertionFactory::createFactory()
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
                try {
                    $derivedAssertionBlock = new CodeBlock();
                    $derivedActionAssertions = $this->fooDerivedAssertionFactory->createForAction($action);

                    foreach ($derivedActionAssertions as $derivedAssertion) {
                        $derivedAssertionBlock->addBlock($this->statementBlockFactory->create($derivedAssertion));
                        $derivedAssertionBlock->addBlock($this->assertionHandler->handle($derivedAssertion));
                    }

                    if (false === $derivedAssertionBlock->isEmpty()) {
                        $block->addBlock($derivedAssertionBlock);
                        $block->addLine(new EmptyLine());
                    }
                } catch (UnsupportedContentException $unsupportedContentException) {
                    throw new UnsupportedStatementException($action, $unsupportedContentException);
                }

                $block->addBlock($this->statementBlockFactory->create($action));
                $block->addBlock($this->actionHandler->handle($action));
                $block->addLine(new EmptyLine());
            }

            foreach ($step->getAssertions() as $assertion) {
                try {
                    $derivedAssertionBlock = new CodeBlock();
                    $derivedAssertionAssertions = $this->fooDerivedAssertionFactory->createForAssertion($assertion);

                    foreach ($derivedAssertionAssertions as $derivedAssertion) {
                        $derivedAssertionBlock->addBlock($this->statementBlockFactory->create($derivedAssertion));
                        $derivedAssertionBlock->addBlock($this->assertionHandler->handle($derivedAssertion));
                    }

//                    $derivedAssertionBlock = $this->derivedAssertionFactory->createForAssertion($assertion);
                    if (false === $derivedAssertionBlock->isEmpty()) {
                        $block->addBlock($derivedAssertionBlock);
                        $block->addLine(new EmptyLine());
                    }
                } catch (UnsupportedContentException $unsupportedContentException) {
                    throw new UnsupportedStatementException($assertion, $unsupportedContentException);
                }

                $block->addBlock($this->statementBlockFactory->create($assertion));
                $block->addBlock($this->assertionHandler->handle($assertion));
                $block->addLine(new EmptyLine());
            }
        } catch (UnsupportedStatementException $unsupportedStatementException) {
            throw new UnsupportedStepException($step, $unsupportedStatementException);
        }

        return $block;
    }
}
