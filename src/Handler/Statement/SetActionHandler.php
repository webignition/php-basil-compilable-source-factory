<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Statement;

use SmartAssert\DomIdentifier\AttributeIdentifierInterface;
use SmartAssert\DomIdentifier\Factory as DomIdentifierFactory;
use SmartAssert\DomIdentifier\FactoryInterface as DomIdentifierFactoryInterface;
use webignition\BaseBasilTestCase\Enum\StatementStage;
use webignition\BasilCompilableSourceFactory\AccessorDefaultValueFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\PhpUnitCallFactory;
use webignition\BasilCompilableSourceFactory\ElementIdentifierSerializer;
use webignition\BasilCompilableSourceFactory\Enum\VariableName as VariableNameEnum;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\DomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\ClassNameCollection;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\NullCoalescerExpression;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilCompilableSourceFactory\Model\VariableName;
use webignition\BasilCompilableSourceFactory\TryCatchBlockFactory;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Model\Action\ActionInterface;
use webignition\BasilModels\Model\StatementInterface;

class SetActionHandler implements StatementHandlerInterface
{
    public function __construct(
        private ScalarValueHandler $scalarValueHandler,
        private DomIdentifierHandler $domIdentifierHandler,
        private AccessorDefaultValueFactory $accessorDefaultValueFactory,
        private DomIdentifierFactoryInterface $domIdentifierFactory,
        private IdentifierTypeAnalyser $identifierTypeAnalyser,
        private ElementIdentifierSerializer $elementIdentifierSerializer,
        private TryCatchBlockFactory $tryCatchBlockFactory,
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
            TryCatchBlockFactory::createFactory(),
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

        $setValueCollectionPlaceholder = new VariableName('setValueCollection');
        $setValueValuePlaceholder = new VariableName('setValueValue');

        $mutationInvocation = new ObjectMethodInvocation(
            new VariableDependency(VariableNameEnum::WEBDRIVER_ELEMENT_MUTATOR),
            'setValue',
            new MethodArguments(
                [
                    $setValueCollectionPlaceholder,
                    $setValueValuePlaceholder,
                ],
                MethodArgumentsInterface::FORMAT_INLINE
            )
        );

        $catchBody = Body::createFromExpressions([
            $this->phpUnitCallFactory->createFailCall($statement, StatementStage::SETUP),
        ]);

        return new StatementHandlerComponents(
            new Body([
                new Statement($mutationInvocation),
                new Statement(
                    $this->phpUnitCallFactory->createCall('refreshCrawlerAndNavigator'),
                ),
            ])
        )->withSetup(
            $this->tryCatchBlockFactory->create(
                Body::createFromExpressions([
                    new AssignmentExpression($setValueCollectionPlaceholder, $collectionAccessor),
                    new AssignmentExpression($setValueValuePlaceholder, $valueAccessor),
                ]),
                new ClassNameCollection([new ClassName(\Throwable::class)]),
                $catchBody,
            ),
        );
    }
}
