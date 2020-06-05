<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSource\Block\CodeBlock;
use webignition\BasilCompilableSource\Block\CodeBlockInterface;
use webignition\BasilCompilableSource\Line\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\Line\Statement\AssignmentStatement;
use webignition\BasilCompilableSource\Line\Statement\Statement;
use webignition\BasilCompilableSource\VariableName;
use webignition\BasilCompilableSourceFactory\ElementIdentifierSerializer;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\DomIdentifierHandler;
use webignition\BasilDomIdentifierFactory\Factory as DomIdentifierFactory;
use webignition\BasilModels\Action\ActionInterface;
use webignition\DomElementIdentifier\AttributeIdentifierInterface;

class InteractionActionHandler
{
    private DomIdentifierHandler $domIdentifierHandler;
    private DomIdentifierFactory $domIdentifierFactory;
    private ElementIdentifierSerializer $elementIdentifierSerializer;

    public function __construct(
        DomIdentifierHandler $domIdentifierHandler,
        DomIdentifierFactory $domIdentifierFactory,
        ElementIdentifierSerializer $elementIdentifierSerializer
    ) {
        $this->domIdentifierHandler = $domIdentifierHandler;
        $this->domIdentifierFactory = $domIdentifierFactory;
        $this->elementIdentifierSerializer = $elementIdentifierSerializer;
    }

    public static function createHandler(): self
    {
        return new InteractionActionHandler(
            DomIdentifierHandler::createHandler(),
            DomIdentifierFactory::createFactory(),
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

        $domIdentifier = $this->domIdentifierFactory->createFromIdentifierString($identifier);
        if (null === $domIdentifier) {
            throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
        }

        if ($domIdentifier instanceof AttributeIdentifierInterface) {
            throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
        }

        $elementPlaceholder = new VariableName('element');

        $accessor = new AssignmentStatement(
            $elementPlaceholder,
            $this->domIdentifierHandler->handleElement(
                $this->elementIdentifierSerializer->serialize($domIdentifier)
            ),
        );

        $invocation = new Statement(new ObjectMethodInvocation(
            $elementPlaceholder,
            $action->getType()
        ));

        return CodeBlock::createEnclosingCodeBlock(new CodeBlock([
            $accessor,
            $invocation,
        ]));
    }
}
