<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSourceFactory\CallFactory\PhpUnitCallFactory;
use webignition\BasilCompilableSourceFactory\ElementIdentifierSerializer;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\DomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Handler\StatementHandlerComponents;
use webignition\BasilCompilableSourceFactory\Handler\StatementHandlerInterface;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\VariableName;
use webignition\BasilDomIdentifierFactory\Factory as DomIdentifierFactory;
use webignition\BasilModels\Model\Action\ActionInterface;
use webignition\BasilModels\Model\StatementInterface;
use webignition\DomElementIdentifier\AttributeIdentifierInterface;

class InteractionActionHandler implements StatementHandlerInterface
{
    public function __construct(
        private DomIdentifierHandler $domIdentifierHandler,
        private DomIdentifierFactory $domIdentifierFactory,
        private ElementIdentifierSerializer $elementIdentifierSerializer,
        private PhpUnitCallFactory $phpUnitCallFactory,
    ) {}

    public static function createHandler(): self
    {
        return new InteractionActionHandler(
            DomIdentifierHandler::createHandler(),
            DomIdentifierFactory::createFactory(),
            ElementIdentifierSerializer::createSerializer(),
            PhpUnitCallFactory::createFactory(),
        );
    }

    /**
     * @throws UnsupportedContentException
     */
    public function handle(StatementInterface $statement): ?StatementHandlerComponents
    {
        if (!$statement instanceof ActionInterface) {
            return null;
        }

        if (!in_array($statement->getType(), ['click', 'submit'])) {
            return null;
        }

        $identifier = (string) $statement->getIdentifier();

        $domIdentifier = $this->domIdentifierFactory->createFromIdentifierString($identifier);
        if (null === $domIdentifier) {
            throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
        }

        if ($domIdentifier instanceof AttributeIdentifierInterface) {
            throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
        }

        $elementPlaceholder = new VariableName('element');

        return new StatementHandlerComponents(
            new Body([
                new Statement(new ObjectMethodInvocation(
                    $elementPlaceholder,
                    $statement->getType()
                )),
                new Statement(
                    $this->phpUnitCallFactory->createCall('refreshCrawlerAndNavigator'),
                ),
            ])
        )->withSetup(
            new Statement(
                new AssignmentExpression(
                    $elementPlaceholder,
                    $this->domIdentifierHandler->handleElement(
                        $this->elementIdentifierSerializer->serialize($domIdentifier)
                    )
                )
            )
        );
    }
}
