<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\DomElementIdentifier\AttributeIdentifierInterface;
use webignition\DomElementIdentifier\ElementIdentifierInterface;

abstract class AbstractNamedDomIdentifier implements NamedDomIdentifierInterface
{
    private $identifier;
    private $placeholder;

    public function __construct(ElementIdentifierInterface $identifier, VariablePlaceholder $placeholder)
    {
        $this->identifier = $identifier;
        $this->placeholder = $placeholder;
    }

    public function getIdentifier(): ElementIdentifierInterface
    {
        return $this->identifier;
    }

    public function getPlaceholder(): VariablePlaceholder
    {
        return $this->placeholder;
    }

    public function includeValue(): bool
    {
        return $this->identifier instanceof AttributeIdentifierInterface;
    }
}
