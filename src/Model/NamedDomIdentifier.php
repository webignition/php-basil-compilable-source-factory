<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

class NamedDomIdentifier extends AbstractNamedDomIdentifier
{
    public function asCollection(): bool
    {
        return true;
    }
}
