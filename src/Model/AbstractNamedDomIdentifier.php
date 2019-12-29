<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilDomIdentifier\DomIdentifier;

abstract class AbstractNamedDomIdentifier implements NamedDomIdentifierInterface
{
    private $identifier;
    private $placeholder;

    public function __construct(DomIdentifier $identifier, VariablePlaceholder $placeholder)
    {
        $this->identifier = $identifier;
        $this->placeholder = $placeholder;
    }

    public function getIdentifier(): DomIdentifier
    {
        return $this->identifier;
    }

    public function getPlaceholder(): VariablePlaceholder
    {
        return $this->placeholder;
    }

    public function includeValue(): bool
    {
        return null !== $this->identifier->getAttributeName();
    }
}
