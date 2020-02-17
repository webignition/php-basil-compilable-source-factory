<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

use webignition\DomElementIdentifier\AttributeIdentifierInterface;
use webignition\DomElementIdentifier\ElementIdentifierInterface;

abstract class AbstractNamedDomIdentifier implements DomIdentifierInterface
{
    private $identifier;

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
        return $this->identifier instanceof AttributeIdentifierInterface;
    }
}
