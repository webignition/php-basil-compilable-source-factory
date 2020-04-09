<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Step;

use webignition\BaseBasilTestCase\Statement as BasilTestStatement;
use webignition\BasilCompilableSource\Line\LiteralExpression;
use webignition\BasilCompilableSource\Line\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSource\Line\MethodInvocation\StaticObjectMethodInvocation;
use webignition\BasilCompilableSource\StaticObject;
use webignition\BasilCompilableSourceFactory\SingleQuotedStringEscaper;
use webignition\BasilModels\Action\ActionInterface;
use webignition\BasilModels\Assertion\DerivedAssertionInterface;
use webignition\BasilModels\StatementInterface as StatementModelInterface;

class StatementInvocationFactory
{
    private $singleQuotedStringEscaper;

    public function __construct(SingleQuotedStringEscaper $singleQuotedStringEscaper)
    {
        $this->singleQuotedStringEscaper = $singleQuotedStringEscaper;
    }

    public static function createFactory(): self
    {
        return new StatementInvocationFactory(
            SingleQuotedStringEscaper::create()
        );
    }

    public function create(
        StatementModelInterface $statement,
        string $argumentFormat = MethodInvocation::ARGUMENT_FORMAT_STACKED
    ): StaticObjectMethodInvocation {
        $serializedStatementSource = (string) json_encode($statement, JSON_PRETTY_PRINT);

        $arguments = [
            new LiteralExpression(sprintf(
                '\'%s\'',
                $this->singleQuotedStringEscaper->escape($serializedStatementSource)
            )),
        ];

        if ($statement instanceof DerivedAssertionInterface) {
            $sourceStatementInvocation = $this->create(
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
