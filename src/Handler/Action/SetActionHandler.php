<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSourceFactory\AccessorDefaultValueFactory;
use webignition\BasilCompilableSourceFactory\ElementIdentifierSerializer;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\DomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\ComparisonExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilDomIdentifierFactory\Factory as DomIdentifierFactory;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Model\Action\ActionInterface;
use webignition\DomElementIdentifier\AttributeIdentifierInterface;

class SetActionHandler
{
    public function __construct(
        private ScalarValueHandler $scalarValueHandler,
        private DomIdentifierHandler $domIdentifierHandler,
        private AccessorDefaultValueFactory $accessorDefaultValueFactory,
        private DomIdentifierFactory $domIdentifierFactory,
        private IdentifierTypeAnalyser $identifierTypeAnalyser,
        private ElementIdentifierSerializer $elementIdentifierSerializer
    ) {}

    public static function createHandler(): self
    {
        return new SetActionHandler(
            ScalarValueHandler::createHandler(),
            DomIdentifierHandler::createHandler(),
            AccessorDefaultValueFactory::createFactory(),
            DomIdentifierFactory::createFactory(),
            IdentifierTypeAnalyser::create(),
            ElementIdentifierSerializer::createSerializer()
        );
    }

    /**
     * @throws UnsupportedContentException
     */
    public function handle(ActionInterface $action): BodyInterface
    {
        $identifier = (string) $action->getIdentifier();

        if (!$this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($identifier)) {
            throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
        }

        $value = (string) $action->getValue();
        $domIdentifier = $this->domIdentifierFactory->createFromIdentifierString($identifier);
        if (null === $domIdentifier) {
            throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
        }

        if ($domIdentifier instanceof AttributeIdentifierInterface) {
            throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
        }

        $collectionAccessor = $this->domIdentifierHandler->handleElementCollection(
            trim($this->elementIdentifierSerializer->serialize($domIdentifier))
        );

        if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($value)) {
            $valueDomIdentifier = $this->domIdentifierFactory->createFromIdentifierString($value);
            if (null === $valueDomIdentifier) {
                throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $value);
            }

            if ($valueDomIdentifier instanceof AttributeIdentifierInterface) {
                $valueAccessor = $this->domIdentifierHandler->handleAttributeValue(
                    $this->elementIdentifierSerializer->serialize($valueDomIdentifier),
                    (string) $valueDomIdentifier->getAttributeName()
                );
            } else {
                $valueAccessor = $this->domIdentifierHandler->handleElementValue(
                    $this->elementIdentifierSerializer->serialize($valueDomIdentifier)
                );
            }
        } else {
            $valueAccessor = $this->scalarValueHandler->handle($value);
        }

        $defaultValue = $this->accessorDefaultValueFactory->createString($value);
        if (null !== $defaultValue) {
            $valueAccessor = new ComparisonExpression(
                $valueAccessor,
                new LiteralExpression((string) $this->accessorDefaultValueFactory->createString($value)),
                '??'
            );
        }

        $mutationInvocation = new ObjectMethodInvocation(
            new VariableDependency(VariableNames::WEBDRIVER_ELEMENT_MUTATOR),
            'setValue',
            new MethodArguments(
                [
                    $collectionAccessor,
                    $valueAccessor
                ],
                MethodArgumentsInterface::FORMAT_STACKED
            )
        );

        return Body::createFromExpressions([$mutationInvocation]);
    }
}
