<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Expression;

class EncapsulatingCastExpression extends CastExpression implements ExpressionInterface
{
    public function __construct(ExpressionInterface $expression, string $castTo)
    {
        parent::__construct(new EncapsulatedExpression($expression), $castTo);
    }
}
