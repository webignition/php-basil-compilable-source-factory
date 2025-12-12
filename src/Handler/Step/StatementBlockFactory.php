<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Step;

use webignition\BasilCompilableSourceFactory\CallFactory\StatementFactoryCallFactory;
use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ObjectPropertyAccessExpression;
use webignition\BasilCompilableSourceFactory\Model\SingleLineComment;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\Statement\StatementInterface;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilModels\Model\EncapsulatingStatementInterface;
use webignition\BasilModels\Model\StatementInterface as StatementModelInterface;

class StatementBlockFactory
{
    public function __construct(
        private StatementFactoryCallFactory $statementFactoryCallFactory
    ) {}

    public static function createFactory(): self
    {
        return new StatementBlockFactory(
            StatementFactoryCallFactory::createFactory()
        );
    }

    public function create(StatementModelInterface $statement): BodyInterface
    {
        $statementCommentContent = $statement->getSource();

        if ($statement instanceof EncapsulatingStatementInterface) {
            $statementCommentContent .= ' <- ' . $statement->getSourceStatement()->getSource();
        }

        return new Body([
            new SingleLineComment($statementCommentContent),
            $this->createAddToHandledStatementsStatement($statement),
        ]);
    }

    private function createAddToHandledStatementsStatement(StatementModelInterface $statement): StatementInterface
    {
        return new Statement(
            new AssignmentExpression(
                new ObjectPropertyAccessExpression(
                    new VariableDependency(VariableName::PHPUNIT_TEST_CASE),
                    'handledStatements[]'
                ),
                $this->statementFactoryCallFactory->create($statement)
            )
        );
    }
}
