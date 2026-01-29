<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Statement;

use SmartAssert\DomIdentifier\AttributeIdentifierInterface;
use SmartAssert\DomIdentifier\ElementIdentifier;
use SmartAssert\DomIdentifier\ElementIdentifierInterface;
use SmartAssert\DomIdentifier\Factory as DomIdentifierFactory;
use webignition\BasilCompilableSourceFactory\ArgumentFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\PhpUnitCallFactory;
use webignition\BasilCompilableSourceFactory\ElementIdentifierSerializer;
use webignition\BasilCompilableSourceFactory\Enum\Type;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\DomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ComparisonExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\CompositeExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\EncapsulatedExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\EncapsulatingCastExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\NullCoalescerExpression;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\Statement\StatementInterface;
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
    public function handle(StatementModelInterface $statement): ?StatementHandlerComponents
    {
        if (!$statement instanceof AssertionInterface) {
            return null;
        }

        $identifier = $statement->getIdentifier();

        $domIdentifier = $this->domIdentifierFactory->createFromIdentifierString((string) $identifier);
        if (null === $domIdentifier) {
            throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
        }

        if ($domIdentifier instanceof AttributeIdentifierInterface) {
            return $this->handleAttributeExistence($statement, $domIdentifier);
        }

        return $this->handleElementExistence($statement, $domIdentifier);
    }

    private function handleElementExistence(
        AssertionInterface $elementExistenceAssertion,
        ElementIdentifierInterface $domIdentifier
    ): StatementHandlerComponents {
        $serializedElementIdentifier = $this->elementIdentifierSerializer->serialize($domIdentifier);

        $examinedAccessor = $this->createDomCrawlerNavigatorCall(
            $domIdentifier,
            $elementExistenceAssertion,
            $this->argumentFactory->create($serializedElementIdentifier)
        );

        $examinedAccessor = EncapsulatingCastExpression::forBool($examinedAccessor);

        $elementExistsVariable = Property::asBooleanVariable('elementExists');
        $elementAssignment = new Statement(
            new AssignmentExpression($elementExistsVariable, $examinedAccessor),
        );

        return new StatementHandlerComponents(
            $this->createAssertionStatement(
                $elementExistenceAssertion,
                $elementExistsVariable,
            )
        )->withSetup(
            $elementAssignment,
        );
    }

    private function handleAttributeExistence(
        AssertionInterface $attributeExistenceAssertion,
        AttributeIdentifierInterface $domIdentifier,
    ): StatementHandlerComponents {
        $elementExistsAssertion = new DerivedValueOperationAssertion(
            $attributeExistenceAssertion,
            (string) ElementIdentifier::fromAttributeIdentifier($domIdentifier),
            'exists',
        );

        $serializedAttributeIdentifier = $this->elementIdentifierSerializer->serialize($domIdentifier);

        $elementAccessor = $this->createDomCrawlerNavigatorCall(
            $domIdentifier,
            $attributeExistenceAssertion,
            $this->argumentFactory->create($serializedAttributeIdentifier)
        );
        $elementAccessor = EncapsulatingCastExpression::forBool($elementAccessor);

        $elementExistsVariable = Property::asBooleanVariable('elementExists');
        $elementAssignment = new Statement(
            new AssignmentExpression($elementExistsVariable, $elementAccessor),
        );

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

        $attributeAccessor = EncapsulatingCastExpression::forBool($attributeAccessor);

        $attributeExistsVariable = Property::asBooleanVariable('attributeExists');
        $attributeAssignment = new Statement(
            new AssignmentExpression(
                $attributeExistsVariable,
                new CompositeExpression(
                    [
                        $elementExistsVariable,
                        LiteralExpression::void(' && '),
                        $attributeAccessor,
                    ],
                    Type::BOOLEAN,
                )
            )
        );

        return new StatementHandlerComponents(
            new Body([
                'element existence assertion' => $this->createAssertionStatement(
                    $elementExistsAssertion,
                    $elementExistsVariable,
                ),
                'attribute existence assertion' => $this->createAssertionStatement(
                    $attributeExistenceAssertion,
                    $attributeExistsVariable,
                ),
            ])
        )->withSetup(
            new Body([
                $elementAssignment,
                $attributeAssignment
            ]),
        );
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

    private function createAssertionStatement(
        AssertionInterface $assertion,
        ExpressionInterface $examinedValuePlaceholder,
    ): StatementInterface {
        $assertionArgumentExpressions = [$examinedValuePlaceholder];
        $assertionMessageExpressions = [
            LiteralExpression::boolean('exists' === $assertion->getOperator()),
            $examinedValuePlaceholder,
        ];

        return new Statement($this->phpUnitCallFactory->createAssertionCall(
            'exists' === $assertion->getOperator() ? 'assertTrue' : 'assertFalse',
            $assertion,
            $assertionArgumentExpressions,
            $assertionMessageExpressions,
        ));
    }
}
