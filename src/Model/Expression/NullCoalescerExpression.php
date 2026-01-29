<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Expression;

use webignition\BasilCompilableSourceFactory\Model\TypeCollection;

class NullCoalescerExpression extends ComparisonExpression
{
    public function __construct(
        private readonly ExpressionInterface $accessor,
        private readonly ExpressionInterface $default,
    ) {
        parent::__construct($accessor, $default, '??');
    }

    public function getType(): TypeCollection
    {
        return $this->accessor->getType()
            ->merge($this->default->getType())
        ;
    }
}
