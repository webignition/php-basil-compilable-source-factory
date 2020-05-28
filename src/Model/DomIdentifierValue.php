<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

use webignition\DomElementIdentifier\AttributeIdentifierInterface;
use webignition\DomElementIdentifier\ElementIdentifierInterface;

class DomIdentifierValue implements DomIdentifierInterface
{
    private ElementIdentifierInterface $identifier;

    public function __construct(ElementIdentifierInterface $identifier)
    {
        $this->identifier = $identifier;
    }

    public function getIdentifier(): ElementIdentifierInterface
    {
        return $this->identifier;
    }

    public function includeValue(): bool
    {
        return true;
    }

    public function asCollection(): bool
    {
        return !$this->getIdentifier() instanceof AttributeIdentifierInterface;
    }
}
