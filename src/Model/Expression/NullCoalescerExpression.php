<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Expression;

class NullCoalescerExpression extends ComparisonExpression
{
    public function __construct(
        private readonly ExpressionInterface $accessor,
        private readonly ExpressionInterface $default,
    ) {
        parent::__construct($accessor, $default, '??');
    }

    public function getType(): array
    {
        $types = [];

        foreach ($this->accessor->getType() as $type) {
            if (!in_array($type, $types)) {
                $types[] = $type;
            }
        }

        foreach ($this->default->getType() as $type) {
            if (!in_array($type, $types)) {
                $types[] = $type;
            }
        }

        return $types;
    }
}
