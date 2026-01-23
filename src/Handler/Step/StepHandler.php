<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Step;

use webignition\BaseBasilTestCase\Enum\StatementStage;
use webignition\BasilCompilableSourceFactory\CallFactory\PhpUnitCallFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStatementException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStepException;
use webignition\BasilCompilableSourceFactory\Handler\Statement\StatementHandler;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\EmptyLine;
use webignition\BasilCompilableSourceFactory\TryCatchBlockFactory;
use webignition\BasilModels\Model\Statement\Action\ActionInterface;
use webignition\BasilModels\Model\Statement\Assertion\AssertionCollectionInterface;
use webignition\BasilModels\Model\Statement\Assertion\AssertionInterface;
use webignition\BasilModels\Model\Statement\Assertion\UniqueAssertionCollection;
use webignition\BasilModels\Model\Statement\StatementCollection;
use webignition\BasilModels\Model\Statement\StatementCollectionInterface;
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

        $derivedAssertions = $this->createDerivedAssertionsCollection($step, $statements);

        $statements = $statements->prepend($derivedAssertions);

        try {
            foreach ($statements as $statement) {
                $bodySources[] = $this->statementBlockFactory->create($statement);

                $handlerComponents = $this->statementHandler->handle($statement);
                $setupBlock = $handlerComponents->getSetup();

                if (null !== $setupBlock) {
                    $setupTryCatchBlock = $this->tryCatchBlockFactory->createForThrowable(
                        $setupBlock,
                        Body::createFromExpressions([
                            $this->phpUnitCallFactory->createFailCall($statement, StatementStage::SETUP),
                        ]),
                    );

                    $bodySources[] = $setupTryCatchBlock;
                    $bodySources[] = new EmptyLine();
                }

                $bodyBlock = $handlerComponents->getBody();
                if ($statement instanceof ActionInterface) {
                    $bodyBlock = $this->tryCatchBlockFactory->createForThrowable(
                        $handlerComponents->getBody(),
                        Body::createFromExpressions([
                            $this->phpUnitCallFactory->createFailCall($statement, StatementStage::EXECUTE),
                        ])
                    );
                }

                $bodySources[] = $bodyBlock;
                $bodySources[] = new EmptyLine();
            }
        } catch (UnsupportedStatementException $unsupportedStatementException) {
            throw new UnsupportedStepException($step, $unsupportedStatementException);
        }

        return new Body($bodySources);
    }

    /**
     * @throws UnsupportedStepException
     */
    private function createDerivedAssertionsCollection(
        StepInterface $step,
        StatementCollectionInterface $statements
    ): AssertionCollectionInterface {
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

        return $derivedAssertions;
    }
}
