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
use webignition\BasilModels\Assertion\UniqueAssertionCollection;
use webignition\BasilModels\Step\StepInterface;

class StepHandler
{
    private ActionHandler $actionHandler;
    private AssertionHandler $assertionHandler;
    private StatementBlockFactory $statementBlockFactory;
    private DerivedAssertionFactory $derivedAssertionFactory;

    public function __construct(
        ActionHandler $actionHandler,
        AssertionHandler $assertionHandler,
        StatementBlockFactory $statementBlockFactory,
        DerivedAssertionFactory $derivedAssertionFactory
    ) {
        $this->actionHandler = $actionHandler;
        $this->assertionHandler = $assertionHandler;
        $this->statementBlockFactory = $statementBlockFactory;
        $this->derivedAssertionFactory = $derivedAssertionFactory;
    }

    public static function createHandler(): StepHandler
    {
        return new StepHandler(
            ActionHandler::createHandler(),
            AssertionHandler::createHandler(),
            StatementBlockFactory::createFactory(),
            DerivedAssertionFactory::createFactory()
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
        $blockSources = [];

        try {
            foreach ($step->getActions() as $action) {
                try {
                    $derivedActionAssertions = $this->derivedAssertionFactory->createForAction($action);

                    $derivedAssertionBlockSources = [];
                    foreach ($derivedActionAssertions as $derivedAssertion) {
                        $derivedAssertionBlockSources[] = $this->statementBlockFactory->create($derivedAssertion);
                        $derivedAssertionBlockSources[] = $this->assertionHandler->handle($derivedAssertion);
                    }

                    if ([] !== $derivedAssertionBlockSources) {
                        $blockSources[] = new CodeBlock($derivedAssertionBlockSources);
                        $blockSources[] = new EmptyLine();
                    }
                } catch (UnsupportedContentException $unsupportedContentException) {
                    throw new UnsupportedStatementException($action, $unsupportedContentException);
                }

                $blockSources[] = $this->statementBlockFactory->create($action);
                $blockSources[] = $this->actionHandler->handle($action);
                $blockSources[] = new EmptyLine();
            }

            $stepAssertions = $step->getAssertions();

            $derivedAssertionAssertions = new UniqueAssertionCollection();
            foreach ($stepAssertions as $assertion) {
                try {
                    $derivedAssertionAssertions = $derivedAssertionAssertions->merge(
                        $this->derivedAssertionFactory->createForAssertion($assertion)
                    );
                } catch (UnsupportedContentException $unsupportedContentException) {
                    throw new UnsupportedStatementException($assertion, $unsupportedContentException);
                }
            }

            $derivedAssertionBlockSources = [];
            foreach ($derivedAssertionAssertions as $derivedAssertion) {
                $derivedAssertionBlockSources[] = $this->statementBlockFactory->create($derivedAssertion);
                $derivedAssertionBlockSources[] = $this->assertionHandler->handle($derivedAssertion);
            }

            if ([] !== $derivedAssertionBlockSources) {
                $blockSources[] = new CodeBlock($derivedAssertionBlockSources);
                $blockSources[] = new EmptyLine();
            }

            foreach ($stepAssertions as $assertion) {
                $blockSources[] = $this->statementBlockFactory->create($assertion);
                $blockSources[] = $this->assertionHandler->handle($assertion);
                $blockSources[] = new EmptyLine();
            }
        } catch (UnsupportedStatementException $unsupportedStatementException) {
            throw new UnsupportedStepException($step, $unsupportedStatementException);
        }

        return new CodeBlock($blockSources);
    }
}
