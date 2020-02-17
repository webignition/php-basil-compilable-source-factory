<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

class DomIdentifier extends AbstractDomIdentifier
{
    public function asCollection(): bool
    {
        return true;
    }
}
