<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Construct;

class ReturnConstruct implements \Stringable
{
    public function __toString(): string
    {
        return 'return';
    }
}
