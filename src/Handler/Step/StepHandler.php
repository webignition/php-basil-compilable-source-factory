<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Step;

use webignition\BaseBasilTestCase\Enum\StatementStage;
use webignition\BasilCompilableSourceFactory\CallFactory\PhpUnitCallFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStatementException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStepException;
use webignition\BasilCompilableSourceFactory\Handler\Statement\StatementHandler;
use webignition\BasilCompilableSourceFactory\Handler\Statement\StatementHandlerComponents;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\EmptyLine;
use webignition\BasilCompilableSourceFactory\TryCatchBlockFactory;
use webignition\BasilModels\Model\Statement\Action\ActionInterface;
use webignition\BasilModels\Model\Statement\Assertion\AssertionInterface;
use webignition\BasilModels\Model\Statement\Assertion\UniqueAssertionCollection;
use webignition\BasilModels\Model\Statement\StatementCollection;
use webignition\BasilModels\Model\Step\StepInterface;

class StepHandler
{
    public function __construct(
        private StatementHandler $statementHandler,
        private StatementBlockFactory $statementBlockFactory,
        private DerivedAssertionFactory $derivedAssertionFactory,
        private TryCatchBlockFactory $tryCatchBlockFactory,
        private PhpUnitCallFactory $phpUnitCallFactory,
    ) {}

    public static function createHandler(): StepHandler
    {
        return new StepHandler(
            StatementHandler::createHandler(),
            StatementBlockFactory::createFactory(),
            DerivedAssertionFactory::createFactory(),
            TryCatchBlockFactory::createFactory(),
            PhpUnitCallFactory::createFactory(),
        );
    }

    /**
     * @throws UnsupportedStepException
     */
    public function handle(StepInterface $step): BodyInterface
    {
        $bodySources = [];

        $statements = new StatementCollection([])
            ->append($step->getActions())
            ->append($step->getAssertions())
        ;

        $derivedAssertions = new UniqueAssertionCollection([]);

        foreach ($statements as $statement) {
            try {
                if ($statement instanceof ActionInterface) {
                    $derivedAssertions = $derivedAssertions->append(
                        $this->derivedAssertionFactory->createForAction($statement)
                    );
                }

                if ($statement instanceof AssertionInterface) {
                    $derivedAssertions = $derivedAssertions->append(
                        $this->derivedAssertionFactory->createForAssertion($statement)
                    );
                }
            } catch (UnsupportedContentException $unsupportedContentException) {
                throw new UnsupportedStepException(
                    $step,
                    new UnsupportedStatementException($statement, $unsupportedContentException)
                );
            }
        }

        try {
            foreach ($derivedAssertions as $derivedAssertion) {
                $bodySources[] = $this->statementBlockFactory->create($derivedAssertion);
                $bodySources[] = $this->createBodyFromStatementHandlerComponents(
                    $this->statementHandler->handle($derivedAssertion)
                );
                $bodySources[] = new EmptyLine();
            }

            foreach ($step->getActions() as $action) {
                if (!$action instanceof ActionInterface) {
                    continue;
                }

                $bodySources[] = $this->statementBlockFactory->create($action);

                $handlerComponents = $this->statementHandler->handle($action);
                $setupBlock = $handlerComponents->getSetup();

                if (null !== $setupBlock) {
                    $setupTryCatchBlock = $this->tryCatchBlockFactory->createForThrowable(
                        $setupBlock,
                        Body::createFromExpressions([
                            $this->phpUnitCallFactory->createFailCall($action, StatementStage::SETUP),
                        ]),
                    );

                    $bodySources[] = $setupTryCatchBlock;
                    $bodySources[] = new EmptyLine();
                }

                $tryCatchBlock = $this->tryCatchBlockFactory->createForThrowable(
                    $handlerComponents->getBody(),
                    Body::createFromExpressions([
                        $this->phpUnitCallFactory->createFailCall($action, StatementStage::EXECUTE),
                    ])
                );

                $bodySources[] = $tryCatchBlock;
                $bodySources[] = new EmptyLine();
            }

            foreach ($step->getAssertions() as $assertion) {
                $bodySources[] = $this->statementBlockFactory->create($assertion);

                $handlerComponents = $this->statementHandler->handle($assertion);
                $setupBlock = $handlerComponents->getSetup();

                if (null !== $setupBlock) {
                    $setupTryCatchBlock = $this->tryCatchBlockFactory->createForThrowable(
                        $setupBlock,
                        Body::createFromExpressions([
                            $this->phpUnitCallFactory->createFailCall($assertion, StatementStage::SETUP),
                        ]),
                    );

                    $bodySources[] = $setupTryCatchBlock;
                    $bodySources[] = new EmptyLine();
                }

                $bodySources[] = $handlerComponents->getBody();
                $bodySources[] = new EmptyLine();
            }
        } catch (UnsupportedStatementException $unsupportedStatementException) {
            throw new UnsupportedStepException($step, $unsupportedStatementException);
        }

        return new Body($bodySources);
    }

    private function createBodyFromStatementHandlerComponents(StatementHandlerComponents $components): BodyInterface
    {
        $parts = [];
        $setup = $components->getSetup();
        if ($setup instanceof BodyInterface) {
            $parts[] = $setup;
            $parts[] = new EmptyLine();
        }

        $parts[] = $components->getBody();

        return new Body($parts);
    }
}
