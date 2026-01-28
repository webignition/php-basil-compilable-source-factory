<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use SmartAssert\DomIdentifier\AttributeIdentifierInterface;
use SmartAssert\DomIdentifier\Factory as DomIdentifierFactory;
use SmartAssert\DomIdentifier\FactoryInterface as DomIdentifierFactoryInterface;
use webignition\BasilCompilableSourceFactory\Enum\Type;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\DomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilableSourceFactory\Model\Expression\ClosureExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\NullableExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\NullCoalescerExpression;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;

class ValueAccessorFactory
{
    public function __construct(
        private IdentifierTypeAnalyser $identifierTypeAnalyser,
        private DomIdentifierFactoryInterface $domIdentifierFactory,
        private DomIdentifierHandler $domIdentifierHandler,
        private ElementIdentifierSerializer $elementIdentifierSerializer,
        private ScalarValueHandler $scalarValueHandler,
        private AccessorDefaultValueFactory $accessorDefaultValueFactory
    ) {}

    public static function createFactory(): self
    {
        return new ValueAccessorFactory(
            IdentifierTypeAnalyser::create(),
            DomIdentifierFactory::createFactory(),
            DomIdentifierHandler::createHandler(),
            ElementIdentifierSerializer::createSerializer(),
            ScalarValueHandler::createHandler(),
            AccessorDefaultValueFactory::createFactory()
        );
    }

    /**
     * @throws UnsupportedContentException
     */
    public function create(string $value): ExpressionInterface
    {
        if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($value)) {
            $identifier = $this->domIdentifierFactory->createFromIdentifierString($value);
            if (null === $identifier) {
                throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $value);
            }

            if ($identifier instanceof AttributeIdentifierInterface) {
                return $this->domIdentifierHandler->handleAttributeValue(
                    $this->elementIdentifierSerializer->serialize($identifier),
                    $identifier->getAttributeName()
                );
            }

            return $this->domIdentifierHandler->handleElementValue(
                $this->elementIdentifierSerializer->serialize($identifier)
            );
        }

        return $this->scalarValueHandler->handle($value);
    }

    /**
     * @throws UnsupportedContentException
     */
    public function createWithDefaultIfNull(string $value): ExpressionInterface
    {
        $accessor = $this->create($value);
        if ($accessor instanceof ClosureExpression) {
            return $accessor;
        }

        $defaultValue = $this->accessorDefaultValueFactory->createString($value);
        if (null === $defaultValue && $accessor instanceof NullableExpressionInterface) {
            $defaultValue = 'null';
        }

        if (null !== $defaultValue) {
            $accessor = new NullCoalescerExpression(
                $accessor,
                new LiteralExpression($defaultValue, Type::STRING)
            );
        }

        return $accessor;
    }
}
