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
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\ClassNameCollection;
use webignition\BasilCompilableSourceFactory\Model\EmptyLine;
use webignition\BasilCompilableSourceFactory\TryCatchBlockFactory;
use webignition\BasilModels\Model\Statement\Action\ActionInterface;
use webignition\BasilModels\Model\Statement\Assertion\AssertionCollectionInterface;
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
            $bodySources[] = $this->createDerivedAssertionsBody($derivedAssertions);

            foreach ($step->getActions() as $action) {
                if (!$action instanceof ActionInterface) {
                    continue;
                }

                $bodySources[] = $this->statementBlockFactory->create($action);
                $actionBody = $this->createBodyFromStatementHandlerComponents(
                    $this->statementHandler->handle($action)
                );

                $failBody = Body::createFromExpressions([
                    $this->phpUnitCallFactory->createFailCall($action, StatementStage::EXECUTE),
                ]);

                $tryCatchBlock = $this->tryCatchBlockFactory->create(
                    $actionBody,
                    new ClassNameCollection([new ClassName(\Throwable::class)]),
                    $failBody,
                );

                $bodySources[] = $tryCatchBlock;
                $bodySources[] = new EmptyLine();
            }

            foreach ($step->getAssertions() as $assertion) {
                $bodySources[] = $this->statementBlockFactory->create($assertion);
                $bodySources[] = $this->createBodyFromStatementHandlerComponents(
                    $this->statementHandler->handle($assertion)
                );
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
    private function createDerivedAssertionsBody(AssertionCollectionInterface $assertions): BodyInterface
    {
        $derivedAssertionBlockSources = [];
        foreach ($assertions as $assertion) {
            $derivedAssertionBlockSources[] = $this->statementBlockFactory->create($assertion);
            $derivedAssertionBlockSources[] = $this->createBodyFromStatementHandlerComponents(
                $this->statementHandler->handle($assertion)
            );
        }

        if ([] !== $derivedAssertionBlockSources) {
            $derivedAssertionBlockSources[] = new EmptyLine();
        }

        return new Body($derivedAssertionBlockSources);
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
