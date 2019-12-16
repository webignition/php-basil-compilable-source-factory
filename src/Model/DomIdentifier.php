<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

use webignition\DomElementLocator\ElementLocator;

class DomIdentifier extends ElementLocator
{
    /**
     * @var string|null
     */
    private $attributeName = null;

    /**
     * @var DomIdentifier
     */
    private $parentIdentifier;

    public function getParentIdentifier(): ?DomIdentifier
    {
        return $this->parentIdentifier;
    }

    public function withParentIdentifier(DomIdentifier $parentIdentifier): DomIdentifier
    {
        $new = clone $this;
        $new->parentIdentifier = $parentIdentifier;

        return $new;
    }

    public function getAttributeName(): ?string
    {
        return $this->attributeName;
    }

    public function withAttributeName(string $attributeName): DomIdentifier
    {
        $new = clone $this;
        $new->attributeName = $attributeName;

        return $new;
    }
}
