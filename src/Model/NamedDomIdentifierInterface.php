<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

use webignition\BasilCompilationSource\VariablePlaceholder;

interface NamedDomIdentifierInterface
{
    public function getIdentifier(): DomIdentifier;
    public function getPlaceholder(): VariablePlaceholder;
    public function includeValue(): bool;
    public function asCollection(): bool;
}
