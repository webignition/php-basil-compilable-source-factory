<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSource\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\AssertionMethodInvocationFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\ValueTypeIdentifier;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Assertion\AssertionInterface;

class ExistenceAssertionHandler extends AbstractAssertionHandler
{
    public const ASSERT_TRUE_METHOD = 'assertTrue';
    public const ASSERT_FALSE_METHOD = 'assertFalse';

    private IdentifierTypeAnalyser $identifierTypeAnalyser;
    private ValueTypeIdentifier $valueTypeIdentifier;
    private ScalarExistenceAssertionHandler $scalarExistenceAssertionHandler;
    private IdentifierExistenceAssertionHandler $identifierExistenceAssertionHandler;

    private const OPERATOR_TO_ASSERTION_TEMPLATE_MAP = [
        'exists' => self::ASSERT_TRUE_METHOD,
        'not-exists' => self::ASSERT_FALSE_METHOD,
    ];

    public function __construct(
        AssertionMethodInvocationFactory $assertionMethodInvocationFactory,
        IdentifierTypeAnalyser $identifierTypeAnalyser,
        ValueTypeIdentifier $valueTypeIdentifier,
        ScalarExistenceAssertionHandler $scalarExistenceAssertionHandler,
        IdentifierExistenceAssertionHandler $identifierExistenceAssertionHandler
    ) {
        parent::__construct($assertionMethodInvocationFactory);

        $this->identifierTypeAnalyser = $identifierTypeAnalyser;
        $this->valueTypeIdentifier = $valueTypeIdentifier;
        $this->scalarExistenceAssertionHandler = $scalarExistenceAssertionHandler;
        $this->identifierExistenceAssertionHandler = $identifierExistenceAssertionHandler;
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

    protected function getOperationToAssertionTemplateMap(): array
    {
        return self::OPERATOR_TO_ASSERTION_TEMPLATE_MAP;
    }

    /**
     * @param AssertionInterface $assertion
     *
     * @return BodyInterface
     *
     * @throws UnsupportedContentException
     */
    public function handle(AssertionInterface $assertion): BodyInterface
    {
        $identifier = $assertion->getIdentifier();

        if ($this->valueTypeIdentifier->isScalarValue($identifier)) {
            return $this->scalarExistenceAssertionHandler->handle($assertion);
        }

        if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($identifier)) {
            return $this->identifierExistenceAssertionHandler->handle($assertion);
        }

        throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
    }
}
