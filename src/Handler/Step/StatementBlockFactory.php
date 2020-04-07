<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Step;

use webignition\BaseBasilTestCase\Statement as BasilTestStatement;
use webignition\BasilCompilableSource\Block\CodeBlock;
use webignition\BasilCompilableSource\Block\CodeBlockInterface;
use webignition\BasilCompilableSource\Line\EmptyLine;
use webignition\BasilCompilableSource\Line\LiteralExpression;
use webignition\BasilCompilableSource\Line\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSource\Line\MethodInvocation\StaticObjectMethodInvocation;
use webignition\BasilCompilableSource\Line\ObjectPropertyAccessExpression;
use webignition\BasilCompilableSource\Line\SingleLineComment;
use webignition\BasilCompilableSource\Line\Statement\AssignmentStatement;
use webignition\BasilCompilableSource\Line\Statement\StatementInterface;
use webignition\BasilCompilableSource\StaticObject;
use webignition\BasilCompilableSource\VariablePlaceholder;
use webignition\BasilCompilableSourceFactory\SingleQuotedStringEscaper;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModels\Action\ActionInterface;
use webignition\BasilModels\Assertion\DerivedAssertionInterface;
use webignition\BasilModels\StatementInterface as StatementModelInterface;

class StatementBlockFactory
{
    private $singleQuotedStringEscaper;

    public function __construct(SingleQuotedStringEscaper $singleQuotedStringEscaper)
    {
        $this->singleQuotedStringEscaper = $singleQuotedStringEscaper;
    }

    public static function createFactory(): self
    {
        return new StatementBlockFactory(
            SingleQuotedStringEscaper::create()
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
        $block->addLine(new EmptyLine());

        return $block;
    }

    private function createAddToHandledStatementsStatement(StatementModelInterface $statement): StatementInterface
    {
        return new AssignmentStatement(
            new ObjectPropertyAccessExpression(
                VariablePlaceholder::createDependency(VariableNames::PHPUNIT_TEST_CASE),
                'handledStatements[]'
            ),
            $this->createCreateStatementInvocation($statement)
        );
    }

    private function createCreateStatementInvocation(
        StatementModelInterface $statement,
        string $argumentFormat = MethodInvocation::ARGUMENT_FORMAT_STACKED
    ): StaticObjectMethodInvocation {
        $arguments = [
            new LiteralExpression(sprintf(
                '\'%s\'',
                $this->singleQuotedStringEscaper->escape($statement->getSource())
            )),
        ];

        if ($statement instanceof DerivedAssertionInterface) {
            $sourceStatementInvocation = $this->createCreateStatementInvocation(
                $statement->getSourceStatement(),
                MethodInvocation::ARGUMENT_FORMAT_INLINE
            );
            $arguments[] = new LiteralExpression($sourceStatementInvocation->render());
        }

        return new StaticObjectMethodInvocation(
            new StaticObject(BasilTestStatement::class),
            $statement instanceof ActionInterface ? 'createAction' : 'createAssertion',
            $arguments,
            $argumentFormat
        );
    }
}
