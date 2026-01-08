<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;

readonly class AssertionArgument
{
    public function __construct(
        public ExpressionInterface $expression,
        public ?string $type = null,
    ) {}
}
