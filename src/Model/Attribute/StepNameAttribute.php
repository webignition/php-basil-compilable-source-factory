<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Attribute;

use webignition\BaseBasilTestCase\Attribute\StepName;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;

class StepNameAttribute extends Attribute implements AttributeInterface
{
    public function __construct(string $stepName)
    {
        parent::__construct(
            new ClassName(StepName::class),
            new MethodArguments([
                LiteralExpression::string(sprintf("'%s'", $stepName)),
            ])
        );
    }
}
