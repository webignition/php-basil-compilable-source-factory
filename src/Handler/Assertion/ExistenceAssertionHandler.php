<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\AssertionMethodInvocationFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Model\Assertion\AssertionInterface;
use webignition\BasilValueTypeIdentifier\ValueTypeIdentifier;

class ExistenceAssertionHandler extends AbstractAssertionHandler
{
    public const ASSERT_TRUE_METHOD = 'assertTrue';
    public const ASSERT_FALSE_METHOD = 'assertFalse';

    private const OPERATOR_TO_ASSERTION_TEMPLATE_MAP = [
        'exists' => self::ASSERT_TRUE_METHOD,
        'not-exists' => self::ASSERT_FALSE_METHOD,
    ];

    public function __construct(
        AssertionMethodInvocationFactory $assertionMethodInvocationFactory,
        private IdentifierTypeAnalyser $identifierTypeAnalyser,
        private ValueTypeIdentifier $valueTypeIdentifier,
        private ScalarExistenceAssertionHandler $scalarExistenceAssertionHandler,
        private IdentifierExistenceAssertionHandler $identifierExistenceAssertionHandler
    ) {
        parent::__construct($assertionMethodInvocationFactory);
    }

    public static function createHandler(): self
    {
        return new ExistenceAssertionHandler(
            AssertionMethodInvocationFactory::createFactory(),
            IdentifierTypeAnalyser::create(),
            new ValueTypeIdentifier(),
            ScalarExistenceAssertionHandler::createHandler(),
            IdentifierExistenceAssertionHandler::createHandler()
        );
    }

    /**
     * @throws UnsupportedContentException
     */
    public function handle(AssertionInterface $assertion): BodyInterface
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

    protected function getOperationToAssertionTemplateMap(): array
    {
        return self::OPERATOR_TO_ASSERTION_TEMPLATE_MAP;
    }
}
