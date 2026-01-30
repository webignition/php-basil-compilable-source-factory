<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Statement;

use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\HasReturnTypeInterface;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\ReturnableInterface;
use webignition\BasilCompilableSourceFactory\Model\TypeCollection;
use webignition\Stubble\Resolvable\ResolvableInterface;

class ReturnStatement implements ResolvableInterface, StatementInterface, ReturnableInterface
{
    private const RENDER_TEMPLATE = 'return {{ expression }};';

    private ExpressionInterface $expression;

    public function __construct(ExpressionInterface $expression)
    {
        $this->expression = $expression;
    }

    public function getExpression(): ExpressionInterface
    {
        return $this->expression;
    }

    public function getMetadata(): MetadataInterface
    {
        return $this->expression->getMetadata();
    }

    public function getTemplate(): string
    {
        return self::RENDER_TEMPLATE;
    }

    public function getContext(): array
    {
        return [
            'expression' => $this->expression,
        ];
    }

    public function mightThrow(): bool
    {
        return $this->expression->mightThrow();
    }

    public function getReturnType(): TypeCollection
    {
        return $this->expression->getType();
        //        return $expressionType instanceof TypeCollection
        //            ? $expressionType
        //            : TypeCollection::void();
        //
        //        var_dump($this->expression::class);
        //        var_dump($this->expression instanceof HasReturnTypeInterface);
        //
        //        if ($this->expression instanceof HasReturnTypeInterface) {
        //            $returnType = $this->expression->getReturnType();
        //        }
        //
        //
        //
        //        $returnType = TypeCollection::void();
        //        return $returnType;
    }
}
