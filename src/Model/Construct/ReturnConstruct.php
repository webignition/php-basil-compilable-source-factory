<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Construct;

class ReturnConstruct
{
    public function __toString(): string
    {
        return 'return';
    }
}
