<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Statement;

use SmartAssert\DomIdentifier\AttributeIdentifierInterface;
use SmartAssert\DomIdentifier\Factory as DomIdentifierFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\PhpUnitCallFactory;
use webignition\BasilCompilableSourceFactory\ElementIdentifierSerializer;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\DomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyContentCollection;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Model\TypeCollection;
use webignition\BasilModels\Model\Statement\Action\ActionInterface;
use webignition\BasilModels\Model\Statement\StatementInterface;

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
    public function handle(StatementInterface $statement): ?StatementHandlerCollections
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

        $elementVariable = Property::asObjectVariable('element');

        BodyContentCollection::createFromExpressions([
            new MethodInvocation(
                methodName: $statement->getType(),
                arguments: new MethodArguments(),
                mightThrow: true,
                type: TypeCollection::void(),
                parent: $elementVariable,
            ),
            $this->phpUnitCallFactory->createRefreshCrawlerAndNavigatorCall(),
        ]);

        return new StatementHandlerCollections(
            BodyContentCollection::createFromExpressions([
                new MethodInvocation(
                    methodName: $statement->getType(),
                    arguments: new MethodArguments(),
                    mightThrow: true,
                    type: TypeCollection::void(),
                    parent: $elementVariable,
                ),
                $this->phpUnitCallFactory->createRefreshCrawlerAndNavigatorCall(),
            ])
        )->withSetup(
            BodyContentCollection::createFromExpressions([
                new AssignmentExpression(
                    $elementVariable,
                    $this->domIdentifierHandler->handleElement(
                        $this->elementIdentifierSerializer->serialize($domIdentifier)
                    )
                )
            ])
        );
    }
}
