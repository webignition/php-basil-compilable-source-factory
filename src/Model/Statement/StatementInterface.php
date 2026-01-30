<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Statement;

use webignition\BasilCompilableSourceFactory\Model\Body\BodyContentInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\HasMetadataInterface;

interface StatementInterface extends BodyContentInterface, HasMetadataInterface
{
    public function getExpression(): ExpressionInterface;
}
