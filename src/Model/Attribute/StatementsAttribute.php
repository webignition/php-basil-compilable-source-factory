<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Attribute;

use webignition\BaseBasilTestCase\Attribute\Statements;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;

class StatementsAttribute extends Attribute implements AttributeInterface
{
    public function __construct(MethodArgumentsInterface $arguments)
    {
        parent::__construct(
            new ClassName(Statements::class),
            $arguments,
        );
    }
}
