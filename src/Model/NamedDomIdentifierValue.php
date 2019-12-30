<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\DomElementIdentifier\DomIdentifierInterface;

class NamedDomIdentifierValue implements NamedDomIdentifierInterface
{
    private $identifier;
    private $placeholder;

    public function __construct(DomIdentifierInterface $identifier, VariablePlaceholder $placeholder)
    {
        $this->identifier = $identifier;
        $this->placeholder = $placeholder;
    }

    public function getIdentifier(): DomIdentifierInterface
    {
        return $this->identifier;
    }

    public function getPlaceholder(): VariablePlaceholder
    {
        return $this->placeholder;
    }

    public function includeValue(): bool
    {
        return true;
    }

    public function asCollection(): bool
    {
        return null === $this->getIdentifier()->getAttributeName();
    }
}
