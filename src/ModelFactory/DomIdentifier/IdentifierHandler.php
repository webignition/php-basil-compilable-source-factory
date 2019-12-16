<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\ModelFactory\DomIdentifier;

use webignition\BasilCompilableSourceFactory\Model\DomIdentifier;
use webignition\BasilCompilableSourceFactory\ModelFactory\IdentifierStringValueAndPositionExtractor;
use webignition\BasilCompilableSourceFactory\QuotedStringExtractor;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;

class IdentifierHandler
{
    private $quotedStringExtractor;
    private $identifierTypeAnalyser;

    public function __construct(
        QuotedStringExtractor $quotedStringExtractor,
        IdentifierTypeAnalyser $identifierTypeAnalyser
    ) {
        $this->quotedStringExtractor = $quotedStringExtractor;
        $this->identifierTypeAnalyser = $identifierTypeAnalyser;
    }

    public static function createHandler(): IdentifierHandler
    {
        return new IdentifierHandler(
            QuotedStringExtractor::createExtractor(),
            new IdentifierTypeAnalyser()
        );
    }

    public function create(string $identifierString): ?DomIdentifier
    {
        $identifierString = trim($identifierString);

        if (!$this->identifierTypeAnalyser->isDomIdentifier($identifierString)) {
            return null;
        }

        $elementLocatorAndPosition = $identifierString;
        $attributeName = '';

        if ($this->identifierTypeAnalyser->isAttributeIdentifier($identifierString)) {
            list($elementLocatorAndPosition, $attributeName) = $this->extractAttributeNameAndElementIdentifier(
                $identifierString
            );
        }

        list($elementLocatorString, $position) = IdentifierStringValueAndPositionExtractor::extract(
            $elementLocatorAndPosition
        );

        $elementLocatorString = (string) $elementLocatorString;
        $position = (int) $position;

        $elementLocatorString = ltrim($elementLocatorString, '$');
        $elementLocatorString = $this->quotedStringExtractor->getQuotedValue($elementLocatorString);

        $identifier = new DomIdentifier($elementLocatorString, $position);

        if ('' !== $attributeName) {
            $identifier = $identifier->withAttributeName($attributeName);
        }

        return $identifier;
    }

    /**
     * @param string $identifier
     *
     * @return array<int, string>
     */
    private function extractAttributeNameAndElementIdentifier(string $identifier): array
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
