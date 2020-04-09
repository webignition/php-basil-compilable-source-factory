<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Step;

use webignition\BasilCompilableSource\Block\CodeBlock;
use webignition\BasilCompilableSource\Block\CodeBlockInterface;
use webignition\BasilCompilableSource\Line\ObjectPropertyAccessExpression;
use webignition\BasilCompilableSource\Line\SingleLineComment;
use webignition\BasilCompilableSource\Line\Statement\AssignmentStatement;
use webignition\BasilCompilableSource\Line\Statement\StatementInterface;
use webignition\BasilCompilableSource\VariablePlaceholder;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModels\Assertion\DerivedAssertionInterface;
use webignition\BasilModels\StatementInterface as StatementModelInterface;

class StatementBlockFactory
{
    private $statementInvocationFactory;

    public function __construct(StatementInvocationFactory $statementInvocationFactory)
    {
        $this->statementInvocationFactory = $statementInvocationFactory;
    }

    public static function createFactory(): self
    {
        return new StatementBlockFactory(
            StatementInvocationFactory::createFactory()
        );
    }

    public function create(StatementModelInterface $statement): CodeBlockInterface
    {
        $block = new CodeBlock();

        $statementCommentContent = $statement->getSource();

        if ($statement instanceof DerivedAssertionInterface) {
            $statementCommentContent .= ' <- ' . $statement->getSourceStatement()->getSource();
        }

        $block->addLine(new SingleLineComment($statementCommentContent));
        $block->addLine($this->createAddToHandledStatementsStatement($statement));

        return $block;
    }

    private function createAddToHandledStatementsStatement(StatementModelInterface $statement): StatementInterface
    {
        return new AssignmentStatement(
            new ObjectPropertyAccessExpression(
                VariablePlaceholder::createDependency(VariableNames::PHPUNIT_TEST_CASE),
                'handledStatements[]'
            ),
            $this->statementInvocationFactory->create($statement)
        );
    }
}
