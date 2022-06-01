<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Statement;

use webignition\BasilCompilableSourceFactory\Model\Body\BodyContentInterface;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;

interface StatementInterface extends BodyContentInterface, BodyInterface
{
    public function getExpression(): ExpressionInterface;
}
