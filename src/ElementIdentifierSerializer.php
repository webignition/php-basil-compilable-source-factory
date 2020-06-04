<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\DomElementIdentifier\ElementIdentifier;
use webignition\DomElementIdentifier\ElementIdentifierInterface;

class ElementIdentifierSerializer
{
    public static function createSerializer(): self
    {
        return new ElementIdentifierSerializer();
    }

    public function serialize(ElementIdentifierInterface $elementIdentifier): string
    {
        $elementOnlyIdentifier = ElementIdentifier::fromAttributeIdentifier($elementIdentifier);

        return (string) json_encode($elementOnlyIdentifier, JSON_PRETTY_PRINT);
    }
}
