<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;

readonly class AddCSlashesCallFactory
{
    public static function createFactory(): AddCSlashesCallFactory
    {
        return new AddCSlashesCallFactory();
    }

    public function create(ExpressionInterface $expression): MethodInvocation
    {
        return new MethodInvocation(
            'addcslashes',
            new MethodArguments([
                $expression,
                new LiteralExpression('"' . "'" . '"'),
            ]),
        );
    }
}
