<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Step;

use webignition\BasilCompilableSourceFactory\CallFactory\PhpUnitCallFactory;
use webignition\BasilCompilableSourceFactory\Enum\PhpUnitFailReason;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStatementException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStepException;
use webignition\BasilCompilableSourceFactory\FailureMessageFactory;
use webignition\BasilCompilableSourceFactory\Handler\Action\ActionHandler;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\AssertionHandler;
use webignition\BasilCompilableSourceFactory\IndexedAction;
use webignition\BasilCompilableSourceFactory\IndexedAssertion;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\ClassNameCollection;
use webignition\BasilCompilableSourceFactory\Model\EmptyLine;
use webignition\BasilCompilableSourceFactory\TryCatchBlockFactory;
use webignition\BasilModels\Model\Assertion\UniqueAssertionCollection;
use webignition\BasilModels\Model\Step\StepInterface;

class StepHandler
{
    public function __construct(
        private ActionHandler $actionHandler,
        private AssertionHandler $assertionHandler,
        private StatementBlockFactory $statementBlockFactory,
        private DerivedAssertionFactory $derivedAssertionFactory,
        private TryCatchBlockFactory $tryCatchBlockFactory,
        private PhpUnitCallFactory $phpUnitCallFactory,
        private FailureMessageFactory $failureMessageFactory,
    ) {}

    public static function createHandler(): StepHandler
    {
        return new StepHandler(
            ActionHandler::createHandler(),
            AssertionHandler::createHandler(),
            StatementBlockFactory::createFactory(),
            DerivedAssertionFactory::createFactory(),
            TryCatchBlockFactory::createFactory(),
            PhpUnitCallFactory::createFactory(),
            FailureMessageFactory::createFactory(),
        );
    }

    /**
     * @throws UnsupportedStepException
     */
    public function handle(StepInterface $step): BodyInterface
    {
        $bodySources = [];
        $statementIndex = 0;

        try {
            foreach ($step->getActions() as $action) {
                $action = new IndexedAction($action, $statementIndex);

                try {
                    $derivedActionAssertions = $this->derivedAssertionFactory->createForAction($action);
                    $bodySources[] = $this->createDerivedAssertionsBody($derivedActionAssertions);
                } catch (UnsupportedContentException $unsupportedContentException) {
                    throw new UnsupportedStatementException($action, $unsupportedContentException);
                }

                $bodySources[] = $this->statementBlockFactory->create($action);

                $actionBody = $this->actionHandler->handle($action);

                $failBody = Body::createFromExpressions([
                    $this->phpUnitCallFactory->createFailCall(
                        $this->failureMessageFactory->create($action, PhpUnitFailReason::ACTION_FAILED)
                    ),
                ]);

                $tryCatchBlock = $this->tryCatchBlockFactory->create(
                    $actionBody,
                    new ClassNameCollection([new ClassName(\Throwable::class)]),
                    $failBody,
                );

                $bodySources[] = $tryCatchBlock;
                $bodySources[] = new EmptyLine();

                ++$statementIndex;
            }

            $stepAssertions = [];

            $derivedAssertionAssertions = new UniqueAssertionCollection();

            foreach ($step->getAssertions() as $assertion) {
                $assertion = new IndexedAssertion($assertion, $statementIndex);

                try {
                    $derivedAssertionAssertions = $derivedAssertionAssertions->merge(
                        $this->derivedAssertionFactory->createForAssertion($assertion)
                    );
                } catch (UnsupportedContentException $unsupportedContentException) {
                    throw new UnsupportedStatementException($assertion, $unsupportedContentException);
                }

                $stepAssertions[] = $assertion;
                ++$statementIndex;
            }

            $bodySources[] = $this->createDerivedAssertionsBody($derivedAssertionAssertions);

            foreach ($stepAssertions as $assertion) {
                $bodySources[] = $this->statementBlockFactory->create($assertion);
                $bodySources[] = $this->assertionHandler->handle($assertion);
                $bodySources[] = new EmptyLine();
            }
        } catch (UnsupportedStatementException $unsupportedStatementException) {
            throw new UnsupportedStepException($step, $unsupportedStatementException);
        }

        return new Body($bodySources);
    }

    /**
     * @throws UnsupportedStatementException
     */
    private function createDerivedAssertionsBody(UniqueAssertionCollection $assertions): BodyInterface
    {
        $derivedAssertionBlockSources = [];
        foreach ($assertions as $assertion) {
            $derivedAssertionBlockSources[] = $this->statementBlockFactory->create($assertion);
            $derivedAssertionBlockSources[] = $this->assertionHandler->handle($assertion);
        }

        if ([] !== $derivedAssertionBlockSources) {
            $derivedAssertionBlockSources[] = new EmptyLine();
        }

        return new Body($derivedAssertionBlockSources);
    }
}
