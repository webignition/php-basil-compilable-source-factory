<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSource\Block\CodeBlock;
use webignition\BasilCompilableSource\Block\CodeBlockInterface;
use webignition\BasilCompilableSource\Line\ComparisonExpression;
use webignition\BasilCompilableSource\Line\LiteralExpression;
use webignition\BasilCompilableSource\Line\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\Line\Statement\Statement;
use webignition\BasilCompilableSource\VariableDependency;
use webignition\BasilCompilableSourceFactory\AccessorDefaultValueFactory;
use webignition\BasilCompilableSourceFactory\ElementIdentifierSerializer;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\DomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilDomIdentifierFactory\Factory as DomIdentifierFactory;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Action\ActionInterface;
use webignition\DomElementIdentifier\AttributeIdentifierInterface;

class SetActionHandler
{
    private ScalarValueHandler $scalarValueHandler;
    private DomIdentifierHandler $domIdentifierHandler;
    private AccessorDefaultValueFactory $accessorDefaultValueFactory;
    private DomIdentifierFactory $domIdentifierFactory;
    private IdentifierTypeAnalyser $identifierTypeAnalyser;
    private ElementIdentifierSerializer $elementIdentifierSerializer;

    public function __construct(
        ScalarValueHandler $scalarValueHandler,
        DomIdentifierHandler $domIdentifierHandler,
        AccessorDefaultValueFactory $accessorDefaultValueFactory,
        DomIdentifierFactory $domIdentifierFactory,
        IdentifierTypeAnalyser $identifierTypeAnalyser,
        ElementIdentifierSerializer $elementIdentifierSerializer
    ) {
        $this->scalarValueHandler = $scalarValueHandler;
        $this->domIdentifierHandler = $domIdentifierHandler;
        $this->accessorDefaultValueFactory = $accessorDefaultValueFactory;
        $this->domIdentifierFactory = $domIdentifierFactory;
        $this->identifierTypeAnalyser = $identifierTypeAnalyser;
        $this->elementIdentifierSerializer = $elementIdentifierSerializer;
    }

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
     * @param ActionInterface $action
     *
     * @return CodeBlockInterface
     *
     * @throws UnsupportedContentException
     */
    public function handle(ActionInterface $action): CodeBlockInterface
    {
        $identifier = $action->getIdentifier();

        if (!$this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($identifier)) {
            throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
        }

        $value = $action->getValue();
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
            if (null ===  $valueDomIdentifier) {
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
                new LiteralExpression($this->accessorDefaultValueFactory->createString($value)),
                '??'
            );
        }

        $mutationCall = new Statement(
            new ObjectMethodInvocation(
                new VariableDependency(VariableNames::WEBDRIVER_ELEMENT_MUTATOR),
                'setValue',
                [
                    $collectionAccessor,
                    $valueAccessor
                ],
                ObjectMethodInvocation::ARGUMENT_FORMAT_STACKED
            )
        );

        return new CodeBlock([
            $mutationCall
        ]);
    }
}
