<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Expression;

class NullCoalescerExpression extends ComparisonExpression
{
    public function __construct(ExpressionInterface $accessor, ExpressionInterface $default)
    {
        parent::__construct($accessor, $default, '??');
    }
}
