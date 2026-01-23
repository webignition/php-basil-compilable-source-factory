<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Expression;

use webignition\BasilCompilableSourceFactory\Model\IsAssigneeInterface;

interface AssignmentExpressionInterface extends ExpressionInterface
{
    public function getAssignee(): IsAssigneeInterface;

    public function getValue(): ExpressionInterface;

    public function getOperator(): string;
}
