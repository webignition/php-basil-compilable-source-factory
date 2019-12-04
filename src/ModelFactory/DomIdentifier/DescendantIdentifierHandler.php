<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\ModelFactory\DomIdentifier;

use webignition\BasilCompilableSourceFactory\Model\DomIdentifier;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;

class DescendantIdentifierHandler
{
    private $identifierHandler;
    private $identifierTypeAnalyser;

    public function __construct(IdentifierHandler $identifierHandler, IdentifierTypeAnalyser $identifierTypeAnalyser)
    {
        $this->identifierHandler = $identifierHandler;
        $this->identifierTypeAnalyser = $identifierTypeAnalyser;
    }

    public static function createHandler(): DescendantIdentifierHandler
    {
        return new DescendantIdentifierHandler(
            IdentifierHandler::createHandler(),
            new IdentifierTypeAnalyser()
        );
    }

    public function create(string $identifierString): ?DomIdentifier
    {
        $identifierString = trim($identifierString);

        if (!$this->identifierTypeAnalyser->isDescendantDomIdentifier($identifierString)) {
            return null;
        }

        $parentIdentifierStringMatches = [];
        preg_match(
            IdentifierTypeAnalyser::PARENT_PREFIX_REGEX,
            $identifierString,
            $parentIdentifierStringMatches
        );

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
