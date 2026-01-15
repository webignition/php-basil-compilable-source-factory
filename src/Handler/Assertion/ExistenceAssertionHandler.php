<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Model\Assertion\AssertionInterface;
use webignition\BasilValueTypeIdentifier\ValueTypeIdentifier;

class ExistenceAssertionHandler
{
    public const ASSERT_TRUE_METHOD = 'assertTrue';
    public const ASSERT_FALSE_METHOD = 'assertFalse';

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
     * @return array{'setup': BodyInterface, 'body': BodyInterface}
     *
     * @throws UnsupportedContentException
     */
    public function handle(AssertionInterface $assertion): array
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
