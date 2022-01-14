<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSource\Body\Body;
use webignition\BasilCompilableSource\Body\BodyInterface;
use webignition\BasilCompilableSource\Expression\AssignmentExpression;
use webignition\BasilCompilableSource\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\Statement\Statement;
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
     * @throws UnsupportedContentException
     */
    public function handle(ActionInterface $action): BodyInterface
    {
        $identifier = (string) $action->getIdentifier();

        $domIdentifier = $this->domIdentifierFactory->createFromIdentifierString($identifier);
        if (null === $domIdentifier) {
            throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
        }

        if ($domIdentifier instanceof AttributeIdentifierInterface) {
            throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
        }

        $elementPlaceholder = new VariableName('element');

        $accessor = new Statement(
            new AssignmentExpression(
                $elementPlaceholder,
                $this->domIdentifierHandler->handleElement(
                    $this->elementIdentifierSerializer->serialize($domIdentifier)
                )
            )
        );

        $invocation = new Statement(new ObjectMethodInvocation(
            $elementPlaceholder,
            $action->getType()
        ));

        return Body::createEnclosingBody(new Body([
            $accessor,
            $invocation,
        ]));
    }
}
