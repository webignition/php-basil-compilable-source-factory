<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSource\Block\CodeBlock;
use webignition\BasilCompilableSource\Block\CodeBlockInterface;
use webignition\BasilCompilableSource\Line\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\Line\Statement\AssignmentStatement;
use webignition\BasilCompilableSource\Line\Statement\Statement;
use webignition\BasilCompilableSource\VariablePlaceholder;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\DomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Model\DomElementIdentifier;
use webignition\BasilDomIdentifierFactory\Factory as DomIdentifierFactory;
use webignition\BasilModels\Action\ActionInterface;
use webignition\DomElementIdentifier\AttributeIdentifierInterface;

class InteractionActionHandler
{
    private DomIdentifierHandler $domIdentifierHandler;
    private DomIdentifierFactory $domIdentifierFactory;

    public function __construct(
        DomIdentifierHandler $domIdentifierHandler,
        DomIdentifierFactory $domIdentifierFactory
    ) {
        $this->domIdentifierHandler = $domIdentifierHandler;
        $this->domIdentifierFactory = $domIdentifierFactory;
    }

    public static function createHandler(): InteractionActionHandler
    {
        return new InteractionActionHandler(
            DomIdentifierHandler::createHandler(),
            DomIdentifierFactory::createFactory()
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

        $elementPlaceholder = VariablePlaceholder::createExport('ELEMENT');

        $accessor = new AssignmentStatement(
            $elementPlaceholder,
            $this->domIdentifierHandler->handle(
                new DomElementIdentifier($domIdentifier)
            )
        );

        $invocation = new Statement(new ObjectMethodInvocation(
            $elementPlaceholder,
            $action->getType()
        ));

        return new CodeBlock([
            $accessor,
            $invocation,
        ]);
    }
}
