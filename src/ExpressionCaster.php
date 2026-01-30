<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSourceFactory\Enum\Type;
use webignition\BasilCompilableSourceFactory\Model\Expression\CastExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\EncapsulatedExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\TypeCollection;

class ExpressionCaster
{
    public function cast(ExpressionInterface $expression, Type $castTo): ExpressionInterface
    {
        if ($expression->getType()->equals(new TypeCollection([$castTo]))) {
            return $expression;
        }

        if ($expression->encapsulateWhenCasting()) {
            $expression = new EncapsulatedExpression($expression);
        }

        return new CastExpression($expression, $castTo);
    }
}
