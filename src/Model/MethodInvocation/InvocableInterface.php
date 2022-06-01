<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\MethodInvocation;

use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;

interface InvocableInterface extends ExpressionInterface
{
    public function getCall(): string;

    public function getArguments(): MethodArgumentsInterface;
}
