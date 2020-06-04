<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSource\Line\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\DomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilDomIdentifierFactory\Factory as DomIdentifierFactory;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\DomElementIdentifier\AttributeIdentifierInterface;

class ValueAccessorFactory
{
    private IdentifierTypeAnalyser $identifierTypeAnalyser;
    private DomIdentifierFactory $domIdentifierFactory;
    private DomIdentifierHandler $domIdentifierHandler;
    private ElementIdentifierSerializer $elementIdentifierSerializer;
    private ScalarValueHandler $scalarValueHandler;

    public function __construct(
        IdentifierTypeAnalyser $identifierTypeAnalyser,
        DomIdentifierFactory $domIdentifierFactory,
        DomIdentifierHandler $domIdentifierHandler,
        ElementIdentifierSerializer $elementIdentifierSerializer,
        ScalarValueHandler $scalarValueHandler
    ) {
        $this->identifierTypeAnalyser = $identifierTypeAnalyser;
        $this->domIdentifierFactory = $domIdentifierFactory;
        $this->domIdentifierHandler = $domIdentifierHandler;
        $this->elementIdentifierSerializer = $elementIdentifierSerializer;
        $this->scalarValueHandler = $scalarValueHandler;
    }

    public static function createFactory(): self
    {
        return new ValueAccessorFactory(
            IdentifierTypeAnalyser::create(),
            DomIdentifierFactory::createFactory(),
            DomIdentifierHandler::createHandler(),
            ElementIdentifierSerializer::createSerializer(),
            ScalarValueHandler::createHandler()
        );
    }

    /**
     * @param string $value
     *
     * @return ExpressionInterface
     *
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
}
