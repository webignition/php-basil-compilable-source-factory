<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Statement;

use SmartAssert\DomIdentifier\AttributeIdentifierInterface;
use SmartAssert\DomIdentifier\ElementIdentifierInterface;
use SmartAssert\DomIdentifier\Factory as DomIdentifierFactory;
use webignition\BasilCompilableSourceFactory\ArgumentFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\PhpUnitCallFactory;
use webignition\BasilCompilableSourceFactory\ElementIdentifierSerializer;
use webignition\BasilCompilableSourceFactory\Enum\Type;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\DomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyContentCollection;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\CastExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ComparisonExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\CompositeExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\EncapsulatedExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\NullCoalescerExpression;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\TypeCollection;
use webignition\BasilModels\Model\Statement\Action\ActionInterface;
use webignition\BasilModels\Model\Statement\Assertion\AssertionInterface;
use webignition\BasilModels\Model\Statement\Assertion\DerivedValueOperationAssertion;
use webignition\BasilModels\Model\Statement\StatementInterface as StatementModelInterface;

class IdentifierExistenceAssertionHandler implements StatementHandlerInterface
{
    public function __construct(
        private ArgumentFactory $argumentFactory,
        private DomCrawlerNavigatorCallFactory $domCrawlerNavigatorCallFactory,
        private DomIdentifierFactory $domIdentifierFactory,
        private DomIdentifierHandler $domIdentifierHandler,
        private ElementIdentifierSerializer $elementIdentifierSerializer,
        private PhpUnitCallFactory $phpUnitCallFactory,
    ) {}

    public static function createHandler(): self
    {
        return new IdentifierExistenceAssertionHandler(
            ArgumentFactory::createFactory(),
            DomCrawlerNavigatorCallFactory::createFactory(),
            DomIdentifierFactory::createFactory(),
            DomIdentifierHandler::createHandler(),
            ElementIdentifierSerializer::createSerializer(),
            PhpUnitCallFactory::createFactory(),
        );
    }

    /**
     * @throws UnsupportedContentException
     */
    public function handle(StatementModelInterface $statement): ?StatementHandlerCollections
    {
        if (!$statement instanceof AssertionInterface) {
            return null;
        }

        $identifier = $statement->getIdentifier();

        $domIdentifier = $this->domIdentifierFactory->createFromIdentifierString((string) $identifier);
        if (null === $domIdentifier) {
            throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
        }

        $elementCollections = $this->handleElementExistence($statement, $domIdentifier);

        if ($domIdentifier instanceof AttributeIdentifierInterface) {
            return $this->handleAttributeExistence($statement, $domIdentifier, $elementCollections);
        }

        return $elementCollections;
    }

    private function handleElementExistence(
        AssertionInterface $elementExistenceAssertion,
        ElementIdentifierInterface $domIdentifier
    ): StatementHandlerCollections {
        $serializedElementIdentifier = $this->elementIdentifierSerializer->serialize($domIdentifier);

        $examinedAccessor = $this->createDomCrawlerNavigatorCall(
            $domIdentifier,
            $elementExistenceAssertion,
            $this->argumentFactory->create($serializedElementIdentifier)
        );

        $examinedAccessor = new CastExpression($examinedAccessor, Type::BOOLEAN);
        $elementExistsVariable = Property::asBooleanVariable('elementExists');

        return new StatementHandlerCollections(
            BodyContentCollection::createFromExpressions([
                $this->createAssertionExpression(
                    $elementExistenceAssertion,
                    $elementExistsVariable,
                )
            ])
        )->withSetup(
            BodyContentCollection::createFromExpressions([
                new AssignmentExpression($elementExistsVariable, $examinedAccessor),
            ])
        );
    }

    private function handleAttributeExistence(
        AssertionInterface $attributeExistenceAssertion,
        AttributeIdentifierInterface $domIdentifier,
        StatementHandlerCollections $elementCollections,
    ): StatementHandlerCollections {
        $serializedAttributeIdentifier = $this->elementIdentifierSerializer->serialize($domIdentifier);

        $elementExistsVariable = Property::asBooleanVariable('elementExists');

        $attributeNullComparisonExpression = new NullCoalescerExpression(
            $this->domIdentifierHandler->handleAttributeValue(
                $serializedAttributeIdentifier,
                $domIdentifier->getAttributeName()
            ),
            LiteralExpression::null(),
        );

        $attributeAccessor = new ComparisonExpression(
            new EncapsulatedExpression($attributeNullComparisonExpression),
            LiteralExpression::null(),
            '!=='
        );

        $attributeAccessor = new CastExpression($attributeAccessor, Type::BOOLEAN);

        $attributeExistsVariable = Property::asBooleanVariable('attributeExists');
        $attributeAssignment = new AssignmentExpression(
            $attributeExistsVariable,
            new CompositeExpression(
                [
                    $elementExistsVariable,
                    LiteralExpression::void(' && '),
                    $attributeAccessor,
                ],
                TypeCollection::boolean(),
            )
        );

        $collections = new StatementHandlerCollections(
            BodyContentCollection::createFromExpressions([
                'attribute existence assertion' => $this->createAssertionExpression(
                    $attributeExistenceAssertion,
                    $attributeExistsVariable,
                ),
            ])
        );

        $elementSetup = $elementCollections->getSetup();
        if ($elementSetup instanceof BodyContentCollection) {
            $collections = $collections->withSetup(
                $elementSetup->append(
                    new Statement($attributeAssignment),
                ),
            );
        }

        return $collections;
    }

    private function createDomCrawlerNavigatorCall(
        ElementIdentifierInterface $domIdentifier,
        AssertionInterface $assertion,
        ExpressionInterface $expression
    ): ExpressionInterface {
        $isAttributeIdentifier = $domIdentifier instanceof AttributeIdentifierInterface;
        $isDerivedFromInteractionAction = false;

        if ($assertion instanceof DerivedValueOperationAssertion) {
            $sourceStatement = $assertion->getSourceStatement();

            $isDerivedFromInteractionAction
                = $sourceStatement instanceof ActionInterface && $sourceStatement->isInteraction();
        }

        return $isAttributeIdentifier || $isDerivedFromInteractionAction
                ? $this->domCrawlerNavigatorCallFactory->createHasOneCall($expression)
                : $this->domCrawlerNavigatorCallFactory->createHasCall($expression);
    }

    private function createAssertionExpression(
        AssertionInterface $assertion,
        ExpressionInterface $examinedValuePlaceholder,
    ): ExpressionInterface {
        $assertionArgumentExpressions = [$examinedValuePlaceholder];
        $assertionMessageExpressions = [
            LiteralExpression::boolean('exists' === $assertion->getOperator()),
            $examinedValuePlaceholder,
        ];

        return $this->phpUnitCallFactory->createAssertionCall(
            'exists' === $assertion->getOperator() ? 'assertTrue' : 'assertFalse',
            $assertion,
            $assertionArgumentExpressions,
            $assertionMessageExpressions,
        );
    }
}
