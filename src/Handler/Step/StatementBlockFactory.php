<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Step;

use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\SingleLineComment;
use webignition\BasilModels\Model\Statement\EncapsulatingStatementInterface;
use webignition\BasilModels\Model\Statement\StatementInterface as StatementModelInterface;

class StatementBlockFactory
{
    public static function createFactory(): self
    {
        return new StatementBlockFactory();
    }

    public function create(StatementModelInterface $statement): BodyInterface
    {
        $statementCommentContent = $statement->getSource();

        if ($statement instanceof EncapsulatingStatementInterface) {
            $statementCommentContent .= ' <- ' . $statement->getSourceStatement()->getSource();
        }

        return new Body([
            new SingleLineComment($statementCommentContent),
        ]);
    }
}
