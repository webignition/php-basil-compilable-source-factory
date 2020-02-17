<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

use webignition\DomElementIdentifier\ElementIdentifierInterface;

interface NamedDomIdentifierInterface
{
    public function getIdentifier(): ElementIdentifierInterface;
    public function includeValue(): bool;
    public function asCollection(): bool;
}
