<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\ModelFactory;

use webignition\BasilCompilableSourceFactory\Exception\UnknownIdentifierException;
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

    /**
     * @param string $identifierString
     *
     * @return DomIdentifier
     *
     * @throws UnknownIdentifierException
     */
    public function create(string $identifierString): DomIdentifier
    {
        $identifier = $this->createNonDescendantIdentifier($identifierString);
        if ($identifier instanceof DomIdentifier) {
            return $identifier;
        }

        $identifier = $this->createDescendantIdentifier($identifierString);
        if ($identifier instanceof DomIdentifier) {
            return $identifier;
        }

        throw new UnknownIdentifierException($identifierString);
    }

    private function createNonDescendantIdentifier(string $identifierString): ?DomIdentifier
    {
        $identifierString = trim($identifierString);

        if (!IdentifierTypeFinder::isDomIdentifier($identifierString)) {
            return null;
        }

        $elementLocatorAndPosition = $identifierString;
        $attributeName = '';

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

    public function createDescendantIdentifier(string $identifierString): ?DomIdentifier
    {
        $identifierString = trim($identifierString);

        if (!IdentifierTypeFinder::isDescendantDomIdentifier($identifierString)) {
            return null;
        }

        $parentIdentifierStringMatches = [];
        preg_match(IdentifierTypeFinder::PARENT_PREFIX_REGEX, $identifierString, $parentIdentifierStringMatches);

        $parentIdentifierMatch = $parentIdentifierStringMatches[0];
        $parentIdentifierString = trim($parentIdentifierMatch, ' {}');
        $parentIdentifier = $this->createNonDescendantIdentifier($parentIdentifierString);

        $parentIdentifierMatchLength = mb_strlen($parentIdentifierMatch);

        $childIdentifierString = mb_substr($identifierString, $parentIdentifierMatchLength);

        $identifier = $this->createNonDescendantIdentifier($childIdentifierString);

        return $identifier->withParentIdentifier($parentIdentifier);
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
