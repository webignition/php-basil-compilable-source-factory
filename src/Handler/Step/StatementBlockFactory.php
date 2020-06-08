<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Step;

use webignition\BasilCompilableSource\Body\Body;
use webignition\BasilCompilableSource\Body\BodyInterface;
use webignition\BasilCompilableSource\Expression\ObjectPropertyAccessExpression;
use webignition\BasilCompilableSource\SingleLineComment;
use webignition\BasilCompilableSource\Statement\AssignmentStatement;
use webignition\BasilCompilableSource\Statement\StatementInterface;
use webignition\BasilCompilableSource\VariableDependency;
use webignition\BasilCompilableSourceFactory\CallFactory\StatementFactoryCallFactory;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModels\EncapsulatingStatementInterface;
use webignition\BasilModels\StatementInterface as StatementModelInterface;

class StatementBlockFactory
{
    private StatementFactoryCallFactory $statementFactoryCallFactory;

    public function __construct(StatementFactoryCallFactory $statementFactoryCallFactory)
    {
        $this->statementFactoryCallFactory = $statementFactoryCallFactory;
    }

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
        return new AssignmentStatement(
            new ObjectPropertyAccessExpression(
                new VariableDependency(VariableNames::PHPUNIT_TEST_CASE),
                'handledStatements[]'
            ),
            $this->statementFactoryCallFactory->create($statement)
        );
    }
}
