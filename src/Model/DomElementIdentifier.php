<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

class DomElementIdentifier extends AbstractDomIdentifier
{
    public function asCollection(): bool
    {
        return false;
    }
}
