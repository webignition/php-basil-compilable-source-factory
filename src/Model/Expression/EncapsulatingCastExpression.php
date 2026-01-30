<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Expression;

use webignition\BasilCompilableSourceFactory\Enum\Type;
use webignition\BasilCompilableSourceFactory\Model\NeverEncapsulateWhenCastingTrait;

class EncapsulatingCastExpression extends CastExpression implements ExpressionInterface
{
    use NeverEncapsulateWhenCastingTrait;

    public function __construct(ExpressionInterface $expression, Type $castTo)
    {
        parent::__construct(new EncapsulatedExpression($expression), $castTo);
    }

    public static function forString(ExpressionInterface $expression): ExpressionInterface
    {
        return new EncapsulatingCastExpression($expression, Type::STRING);
    }

    public static function forBool(ExpressionInterface $expression): ExpressionInterface
    {
        return new EncapsulatingCastExpression($expression, Type::BOOLEAN);
    }
}
