<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Expression;

class EncapsulatingCastExpression extends CastExpression implements ExpressionInterface
{
    public function __construct(ExpressionInterface $expression, string $castTo)
    {
        parent::__construct(new EncapsulatedExpression($expression), $castTo);
    }

    public static function forString(ExpressionInterface $expression): ExpressionInterface
    {
        return new EncapsulatingCastExpression($expression, 'string');
    }

    public static function forBool(ExpressionInterface $expression): ExpressionInterface
    {
        return new EncapsulatingCastExpression($expression, 'bool');
    }
}
