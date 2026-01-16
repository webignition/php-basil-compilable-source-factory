<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\StatementHandlerComponents;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Model\Assertion\AssertionInterface;
use webignition\BasilValueTypeIdentifier\ValueTypeIdentifier;

class ExistenceAssertionHandler
{
    public function __construct(
        private IdentifierTypeAnalyser $identifierTypeAnalyser,
        private ValueTypeIdentifier $valueTypeIdentifier,
        private ScalarExistenceAssertionHandler $scalarExistenceAssertionHandler,
        private IdentifierExistenceAssertionHandler $identifierExistenceAssertionHandler
    ) {}

    public static function createHandler(): self
    {
        return new ExistenceAssertionHandler(
            IdentifierTypeAnalyser::create(),
            new ValueTypeIdentifier(),
            ScalarExistenceAssertionHandler::createHandler(),
            IdentifierExistenceAssertionHandler::createHandler()
        );
    }

    /**
     * @throws UnsupportedContentException
     */
    public function handle(AssertionInterface $assertion): StatementHandlerComponents
    {
        $identifier = $assertion->getIdentifier();

        if (is_string($identifier) && $this->valueTypeIdentifier->isScalarValue($identifier)) {
            return $this->scalarExistenceAssertionHandler->handle($assertion);
        }

        if (is_string($identifier) && $this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($identifier)) {
            return $this->identifierExistenceAssertionHandler->handle($assertion);
        }

        throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
    }
}
