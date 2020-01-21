<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\NamedDomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Model\NamedDomElementIdentifier;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilDomIdentifierFactory\Factory as DomIdentifierFactory;
use webignition\BasilModels\Action\InteractionActionInterface;
use webignition\DomElementIdentifier\AttributeIdentifierInterface;

class InteractionActionHandler
{
    private $namedDomIdentifierHandler;
    private $domIdentifierFactory;

    public function __construct(
        NamedDomIdentifierHandler $namedDomIdentifierHandler,
        DomIdentifierFactory $domIdentifierFactory
    ) {
        $this->namedDomIdentifierHandler = $namedDomIdentifierHandler;
        $this->domIdentifierFactory = $domIdentifierFactory;
    }

    public static function createHandler(): InteractionActionHandler
    {
        return new InteractionActionHandler(
            NamedDomIdentifierHandler::createHandler(),
            DomIdentifierFactory::createFactory()
        );
    }

    /**
     * @param InteractionActionInterface $action
     *
     * @return CodeBlockInterface
     *
     * @throws UnsupportedContentException
     */
    public function handle(InteractionActionInterface $action): CodeBlockInterface
    {
        $identifier = $action->getIdentifier();

        $domIdentifier = $this->domIdentifierFactory->createFromIdentifierString($identifier);
        if (null === $domIdentifier) {
            throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
        }

        if ($domIdentifier instanceof AttributeIdentifierInterface) {
            throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
        }

        $variableExports = new VariablePlaceholderCollection();
        $elementPlaceholder = $variableExports->create('ELEMENT');

        $accessor = $this->namedDomIdentifierHandler->handle(
            new NamedDomElementIdentifier($domIdentifier, $elementPlaceholder)
        );

        return new CodeBlock([
            $accessor,
            new Statement(sprintf(
                '%s->%s()',
                (string) $elementPlaceholder,
                $action->getType()
            )),
        ]);
    }
}
