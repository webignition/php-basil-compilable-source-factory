<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\ModelFactory\DomIdentifier;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedIdentifierException;
use webignition\BasilDomIdentifier\Model\DomIdentifier;

class DomIdentifierFactory
{
    private $identifierHandler;
    private $descendantIdentifierHandler;

    public function __construct(
        IdentifierHandler $identifierHandler,
        DescendantIdentifierHandler $descendantIdentifierHandler
    ) {
        $this->identifierHandler = $identifierHandler;
        $this->descendantIdentifierHandler = $descendantIdentifierHandler;
    }

    public static function createFactory(): DomIdentifierFactory
    {
        return new DomIdentifierFactory(
            IdentifierHandler::createHandler(),
            DescendantIdentifierHandler::createHandler()
        );
    }

    /**
     * @param string $identifierString
     *
     * @return DomIdentifier
     *
     * @throws UnsupportedIdentifierException
     */
    public function create(string $identifierString): DomIdentifier
    {
        $identifier = $this->identifierHandler->create($identifierString);
        if ($identifier instanceof DomIdentifier) {
            return $identifier;
        }

        $identifier = $this->descendantIdentifierHandler->create($identifierString);
        if ($identifier instanceof DomIdentifier) {
            return $identifier;
        }

        throw new UnsupportedIdentifierException($identifierString);
    }
}
