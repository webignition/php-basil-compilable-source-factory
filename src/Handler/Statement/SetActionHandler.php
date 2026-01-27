<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Statement;

use SmartAssert\DomIdentifier\AttributeIdentifierInterface;
use SmartAssert\DomIdentifier\Factory as DomIdentifierFactory;
use SmartAssert\DomIdentifier\FactoryInterface as DomIdentifierFactoryInterface;
use webignition\BasilCompilableSourceFactory\AccessorDefaultValueFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\PhpUnitCallFactory;
use webignition\BasilCompilableSourceFactory\ElementIdentifierSerializer;
use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\DomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\NullCoalescerExpression;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Model\Statement\Action\ActionInterface;
use webignition\BasilModels\Model\Statement\StatementInterface;

class SetActionHandler implements StatementHandlerInterface
{
    public function __construct(
        private ScalarValueHandler $scalarValueHandler,
        private DomIdentifierHandler $domIdentifierHandler,
        private AccessorDefaultValueFactory $accessorDefaultValueFactory,
        private DomIdentifierFactoryInterface $domIdentifierFactory,
        private IdentifierTypeAnalyser $identifierTypeAnalyser,
        private ElementIdentifierSerializer $elementIdentifierSerializer,
        private PhpUnitCallFactory $phpUnitCallFactory,
    ) {}

    public static function createHandler(): self
    {
        return new SetActionHandler(
            ScalarValueHandler::createHandler(),
            DomIdentifierHandler::createHandler(),
            AccessorDefaultValueFactory::createFactory(),
            DomIdentifierFactory::createFactory(),
            IdentifierTypeAnalyser::create(),
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

        if (!$statement->isInput()) {
            return null;
        }

        $identifier = (string) $statement->getIdentifier();

        if (!$this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($identifier)) {
            throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
        }

        $value = (string) $statement->getValue();
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
            $valueAccessor = new NullCoalescerExpression(
                $valueAccessor,
                new LiteralExpression((string) $this->accessorDefaultValueFactory->createString($value)),
            );
        }

        $setValueCollectionVariable = Property::asVariable('setValueCollection');
        $setValueValueVariable = Property::asVariable('setValueValue');

        $mutationInvocation = new ObjectMethodInvocation(
            object: new VariableDependency(DependencyName::WEBDRIVER_ELEMENT_MUTATOR->value),
            methodName: 'setValue',
            arguments: new MethodArguments(
                [
                    $setValueCollectionVariable,
                    $setValueValueVariable,
                ],
                MethodArgumentsInterface::FORMAT_INLINE
            ),
            mightThrow: true,
        );

        return new StatementHandlerComponents(
            new Body([
                new Statement($mutationInvocation),
                new Statement(
                    $this->phpUnitCallFactory->createRefreshCrawlerAndNavigatorCall(),
                ),
            ])
        )->withSetup(
            Body::createFromExpressions([
                new AssignmentExpression($setValueCollectionVariable, $collectionAccessor),
                new AssignmentExpression($setValueValueVariable, $valueAccessor),
            ]),
        );
    }
}
