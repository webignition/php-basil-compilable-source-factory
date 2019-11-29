<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\ModelFactory\DomIdentifier;

use webignition\BasilCompilableSourceFactory\IdentifierTypeFinder;
use webignition\BasilCompilableSourceFactory\Model\DomIdentifier;

class DescendantIdentifierHandler
{
    private $identifierHandler;

    public function __construct(IdentifierHandler $identifierHandler)
    {
        $this->identifierHandler = $identifierHandler;
    }

    public static function createHandler(): DescendantIdentifierHandler
    {
        return new DescendantIdentifierHandler(
            IdentifierHandler::createHandler()
        );
    }

    public function create(string $identifierString): ?DomIdentifier
    {
        $identifierString = trim($identifierString);

        if (!IdentifierTypeFinder::isDescendantDomIdentifier($identifierString)) {
            return null;
        }

        $parentIdentifierStringMatches = [];
        preg_match(IdentifierTypeFinder::PARENT_PREFIX_REGEX, $identifierString, $parentIdentifierStringMatches);

        $parentIdentifierMatch = $parentIdentifierStringMatches[0];
        $parentIdentifierString = trim($parentIdentifierMatch, ' {}');
        $parentIdentifier = $this->identifierHandler->create($parentIdentifierString);

        if (!$parentIdentifier instanceof DomIdentifier) {
            return null;
        }

        $parentIdentifierMatchLength = mb_strlen($parentIdentifierMatch);

        $childIdentifierString = mb_substr($identifierString, $parentIdentifierMatchLength);

        $identifier = $this->identifierHandler->create($childIdentifierString);

        if (!$identifier instanceof DomIdentifier) {
            return null;
        }

        return $identifier->withParentIdentifier($parentIdentifier);
    }
}
