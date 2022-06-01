<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;

interface VariablePlaceholderInterface extends ExpressionInterface
{
    public function getName(): string;
}
