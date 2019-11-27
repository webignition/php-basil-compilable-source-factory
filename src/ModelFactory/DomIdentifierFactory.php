<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\ModelFactory;

use webignition\BasilCompilableSourceFactory\IdentifierTypeFinder;
use webignition\BasilCompilableSourceFactory\Model\DomIdentifier;
use webignition\BasilCompilableSourceFactory\QuotedStringExtractor;

class DomIdentifierFactory
{
    private $quotedStringExtractor;

    public function __construct(QuotedStringExtractor $quotedStringExtractor)
    {
        $this->quotedStringExtractor = $quotedStringExtractor;
    }

    public static function createFactory(): DomIdentifierFactory
    {
        return new DomIdentifierFactory(
            QuotedStringExtractor::createExtractor()
        );
    }

    public function create(string $identifierString): ?DomIdentifier
    {
        $identifierString = trim($identifierString);
        $elementLocatorAndPosition = $identifierString;
        $attributeName = '';

        if (!IdentifierTypeFinder::isDomIdentifier($identifierString)) {
            return null;
        }

        if (IdentifierTypeFinder::isAttributeIdentifier($identifierString)) {
            list($elementLocatorAndPosition, $attributeName) = $this->extractAttributeNameAndElementIdentifier(
                $identifierString
            );
        }

        list($elementLocatorString, $position) = IdentifierStringValueAndPositionExtractor::extract(
            $elementLocatorAndPosition
        );

        $elementLocatorString = ltrim($elementLocatorString, '$');
        $elementLocatorString = $this->quotedStringExtractor->getQuotedValue($elementLocatorString);

        $identifier = new DomIdentifier($elementLocatorString, $position);

        if ('' !== $attributeName) {
            $identifier = $identifier->withAttributeName($attributeName);
        }

        return $identifier;
    }

    private function extractAttributeNameAndElementIdentifier(string $identifier)
    {
        $lastDotPosition = (int) mb_strrpos($identifier, '.');

        $elementIdentifier = mb_substr($identifier, 0, $lastDotPosition);
        $attributeName = mb_substr($identifier, $lastDotPosition + 1);

        return [
            $elementIdentifier,
            $attributeName
        ];
    }
}
