<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Statement;

use SmartAssert\DomIdentifier\AttributeIdentifierInterface;
use SmartAssert\DomIdentifier\ElementIdentifier;
use SmartAssert\DomIdentifier\ElementIdentifierInterface;
use SmartAssert\DomIdentifier\Factory as DomIdentifierFactory;
use webignition\BaseBasilTestCase\Enum\StatementStage;
use webignition\BasilCompilableSourceFactory\ArgumentFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\PhpUnitCallFactory;
use webignition\BasilCompilableSourceFactory\ElementIdentifierSerializer;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\DomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Model\Block\TryCatch\TryCatchBlock;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\ClassNameCollection;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ComparisonExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\CompositeExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\EncapsulatedExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\EncapsulatingCastExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\NullCoalescerExpression;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\Statement\StatementInterface;
use webignition\BasilCompilableSourceFactory\Model\VariableName;
use webignition\BasilCompilableSourceFactory\TryCatchBlockFactory;
use webignition\BasilModels\Model\Action\ActionInterface;
use webignition\BasilModels\Model\Assertion\AssertionInterface;
use webignition\BasilModels\Model\Assertion\DerivedValueOperationAssertion;
use webignition\BasilModels\Model\StatementInterface as StatementModelInterface;

class IdentifierExistenceAssertionHandler implements StatementHandlerInterface
{
    public function __construct(
        private ArgumentFactory $argumentFactory,
        private DomCrawlerNavigatorCallFactory $domCrawlerNavigatorCallFactory,
        private DomIdentifierFactory $domIdentifierFactory,
        private DomIdentifierHandler $domIdentifierHandler,
        private ElementIdentifierSerializer $elementIdentifierSerializer,
        private TryCatchBlockFactory $tryCatchBlockFactory,
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
            TryCatchBlockFactory::createFactory(),
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
            $this->argumentFactory->createSingular($serializedElementIdentifier)
        );

        $examinedAccessor = EncapsulatingCastExpression::forBool($examinedAccessor);

        $elementExistPlaceholder = new VariableName('elementExists');
        $elementAssignment = new Statement(
            new AssignmentExpression($elementExistPlaceholder, $examinedAccessor),
        );

        return new StatementHandlerComponents(
            $this->createAssertionStatement(
                $elementExistenceAssertion,
                $elementExistPlaceholder,
            )
        )->withSetup(
            $this->createNavigatorHasCallTryCatchBlock(
                $elementAssignment,
                $elementExistenceAssertion
            )
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
            $this->argumentFactory->createSingular($serializedAttributeIdentifier)
        );
        $elementAccessor = EncapsulatingCastExpression::forBool($elementAccessor);

        $elementExistsPlaceholder = new VariableName('elementExists');
        $elementAssignment = new Statement(
            new AssignmentExpression($elementExistsPlaceholder, $elementAccessor),
        );

        $attributeNullComparisonExpression = new NullCoalescerExpression(
            $this->domIdentifierHandler->handleAttributeValue(
                $serializedAttributeIdentifier,
                $domIdentifier->getAttributeName()
            ),
            new LiteralExpression('null'),
        );

        $attributeAccessor = new ComparisonExpression(
            new EncapsulatedExpression($attributeNullComparisonExpression),
            new LiteralExpression('null'),
            '!=='
        );

        $attributeAccessor = EncapsulatingCastExpression::forBool($attributeAccessor);

        $attributeExistsPlaceholder = new VariableName('attributeExists');
        $attributeAssignment = new Statement(
            new AssignmentExpression(
                $attributeExistsPlaceholder,
                new CompositeExpression([
                    $elementExistsPlaceholder,
                    new LiteralExpression(' && '),
                    $attributeAccessor,
                ])
            )
        );

        return new StatementHandlerComponents(
            new Body([
                'element existence assertion' => $this->createAssertionStatement(
                    $elementExistsAssertion,
                    $elementExistsPlaceholder,
                ),
                'attribute existence assertion' => $this->createAssertionStatement(
                    $attributeExistenceAssertion,
                    $attributeExistsPlaceholder,
                ),
            ])
        )->withSetup(
            $this->createNavigatorHasCallTryCatchBlock(
                new Body([$elementAssignment, $attributeAssignment]),
                $elementExistsAssertion
            )
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

    private function createNavigatorHasCallTryCatchBlock(
        BodyInterface $tryBody,
        AssertionInterface $assertion,
    ): TryCatchBlock {
        $catchBody = Body::createFromExpressions([
            $this->phpUnitCallFactory->createFailCall($assertion, StatementStage::SETUP),
        ]);

        return $this->tryCatchBlockFactory->create(
            $tryBody,
            new ClassNameCollection([new ClassName(\Throwable::class)]),
            $catchBody,
        );
    }

    private function createAssertionStatement(
        AssertionInterface $assertion,
        ExpressionInterface $examinedValuePlaceholder,
    ): StatementInterface {
        $assertionArgumentExpressions = [$examinedValuePlaceholder];
        $assertionMessageExpressions = [
            new LiteralExpression(('exists' === $assertion->getOperator()) ? 'true' : 'false'),
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
