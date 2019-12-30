<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\DomElementIdentifier\DomIdentifierInterface;

interface NamedDomIdentifierInterface
{
    public function getIdentifier(): DomIdentifierInterface;
    public function getPlaceholder(): VariablePlaceholder;
    public function includeValue(): bool;
    public function asCollection(): bool;
}
