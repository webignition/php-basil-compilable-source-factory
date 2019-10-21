<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

class NamedDomElementIdentifier extends AbstractNamedDomIdentifier
{
    public function asCollection(): bool
    {
        return false;
    }
}
