<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\ArgumentFactory;
use webignition\BasilCompilableSourceFactory\AssertionArgument;
use webignition\BasilCompilableSourceFactory\AssertionMessageFactory;
use webignition\BasilCompilableSourceFactory\AssertionStatementFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\PhpUnitCallFactory;
use webignition\BasilCompilableSourceFactory\ElementIdentifierSerializer;
use webignition\BasilCompilableSourceFactory\Enum\VariableName as VariableNameEnum;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\FailureMessageFactory;
use webignition\BasilCompilableSourceFactory\Handler\DomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Handler\StatementHandlerComponents;
use webignition\BasilCompilableSourceFactory\Model\Block\TryCatch\TryCatchBlock;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\ClassNameCollection;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ComparisonExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\EncapsulatedExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\NullCoalescerExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\TernaryExpression;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\Statement\StatementInterface;
use webignition\BasilCompilableSourceFactory\Model\VariableName;
use webignition\BasilCompilableSourceFactory\TryCatchBlockFactory;
use webignition\BasilDomIdentifierFactory\Factory as DomIdentifierFactory;
use webignition\BasilModels\Model\Action\ActionInterface;
use webignition\BasilModels\Model\Assertion\AssertionInterface;
use webignition\BasilModels\Model\Assertion\DerivedValueOperationAssertion;
use webignition\DomElementIdentifier\AttributeIdentifierInterface;
use webignition\DomElementIdentifier\ElementIdentifier;
use webignition\DomElementIdentifier\ElementIdentifierInterface;
use webignition\SymfonyDomCrawlerNavigator\Exception\InvalidLocatorException;

class IdentifierExistenceAssertionHandler
{
    public function __construct(
        private ArgumentFactory $argumentFactory,
        private DomCrawlerNavigatorCallFactory $domCrawlerNavigatorCallFactory,
        private DomIdentifierFactory $domIdentifierFactory,
        private DomIdentifierHandler $domIdentifierHandler,
        private ElementIdentifierSerializer $elementIdentifierSerializer,
        private TryCatchBlockFactory $tryCatchBlockFactory,
        private PhpUnitCallFactory $phpUnitCallFactory,
        private AssertionStatementFactory $assertionStatementFactory,
        private FailureMessageFactory $failureMessageFactory,
        private AssertionMessageFactory $assertionMessageFactory,
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
            AssertionStatementFactory::createFactory(),
            FailureMessageFactory::createFactory(),
            AssertionMessageFactory::createFactory(),
        );
    }

    /**
     * @throws UnsupportedContentException
     */
    public function handle(AssertionInterface $assertion): StatementHandlerComponents
    {
        $identifier = $assertion->getIdentifier();

        $domIdentifier = $this->domIdentifierFactory->createFromIdentifierString((string) $identifier);
        if (null === $domIdentifier) {
            throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
        }

        if ($domIdentifier instanceof AttributeIdentifierInterface) {
            return $this->handleAttributeExistence($assertion, $domIdentifier);
        }

        return $this->handleElementExistence($assertion, $domIdentifier);
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

        $examinedValuePlaceholder = new VariableName(VariableNameEnum::EXAMINED_VALUE->value);
        $examinedValueAssignmentStatement = new Statement(
            new AssignmentExpression($examinedValuePlaceholder, $examinedAccessor),
        );

        return new StatementHandlerComponents(
            $this->createAssertionStatement(
                $elementExistenceAssertion,
                $examinedValuePlaceholder,
            )
        )->withSetup(
            $this->createNavigatorHasCallTryCatchBlock(
                $examinedValueAssignmentStatement,
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

        $elementExaminedAccessor = $this->createDomCrawlerNavigatorCall(
            $domIdentifier,
            $attributeExistenceAssertion,
            $this->argumentFactory->createSingular($serializedAttributeIdentifier)
        );

        $examinedValuePlaceholder = new VariableName(VariableNameEnum::EXAMINED_VALUE->value);
        $elementExaminedValueAssignmentStatement = new Statement(
            new AssignmentExpression($examinedValuePlaceholder, $elementExaminedAccessor),
        );

        $attributeNullComparisonExpression = new NullCoalescerExpression(
            $this->domIdentifierHandler->handleAttributeValue(
                $serializedAttributeIdentifier,
                $domIdentifier->getAttributeName()
            ),
            new LiteralExpression('null'),
        );

        $attributeExaminedAccessor = new ComparisonExpression(
            new EncapsulatedExpression($attributeNullComparisonExpression),
            new LiteralExpression('null'),
            '!=='
        );

        $examinedValueAssignment = new AssignmentExpression($examinedValuePlaceholder, $attributeExaminedAccessor);

        $catchBody = Body::createFromExpressions([
            $this->phpUnitCallFactory->createFailCall(
                $this->failureMessageFactory->createForAssertionSetupThrowable($attributeExistenceAssertion)
            ),
        ]);

        $tryCatchBlock = $this->tryCatchBlockFactory->create(
            Body::createFromExpressions([
                $examinedValueAssignment,
            ]),
            new ClassNameCollection([new ClassName(\Throwable::class)]),
            $catchBody,
        );

        return new StatementHandlerComponents(
            new Body([
                'element existence assertion' => $this->createAssertionStatement(
                    $elementExistsAssertion,
                    $examinedValuePlaceholder,
                ),
                'attribute examined value assignment' => $tryCatchBlock,
                'attribute existence assertion' => $this->createAssertionStatement(
                    $attributeExistenceAssertion,
                    $examinedValuePlaceholder,
                ),
            ])
        )->withSetup(
            $this->createNavigatorHasCallTryCatchBlock(
                $elementExaminedValueAssignmentStatement,
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
        StatementInterface $setExaminedValueAssignmentStatement,
        AssertionInterface $assertion,
    ): TryCatchBlock {
        $exceptionVariable = new VariableName('exception');
        $getElementIdentifierInvocation = new ObjectMethodInvocation(
            $exceptionVariable,
            'getElementIdentifier'
        );

        $locatorVariableExpression = new VariableName('locator');
        $locatorAssignmentExpression = new AssignmentExpression(
            $locatorVariableExpression,
            new ObjectMethodInvocation(
                $getElementIdentifierInvocation,
                'getLocator'
            ),
        );

        $typeVariableExpression = new VariableName('type');
        $typeAssignmentExpression = new AssignmentExpression(
            $typeVariableExpression,
            new TernaryExpression(
                new ObjectMethodInvocation(
                    $getElementIdentifierInvocation,
                    'isCssSelector'
                ),
                new LiteralExpression("'css'"),
                new LiteralExpression("'xpath'"),
            ),
        );

        $catchBody = Body::createFromExpressions([
            $locatorAssignmentExpression,
            $typeAssignmentExpression,
            $this->phpUnitCallFactory->createFailCall(
                $this->failureMessageFactory->createForInvalidLocatorException(
                    $assertion,
                    $locatorVariableExpression,
                    $typeVariableExpression,
                )
            ),
        ]);

        return $this->tryCatchBlockFactory->create(
            new Body([$setExaminedValueAssignmentStatement]),
            new ClassNameCollection([new ClassName(InvalidLocatorException::class)]),
            $catchBody,
        );
    }

    private function createAssertionStatement(
        AssertionInterface $assertion,
        ExpressionInterface $examinedValuePlaceholder,
    ): StatementInterface {
        $examined = new AssertionArgument($examinedValuePlaceholder, 'bool');

        $expected = new AssertionArgument(
            new LiteralExpression(('exists' === $assertion->getOperator()) ? 'true' : 'false'),
            'bool'
        );

        return $this->assertionStatementFactory->create(
            assertionMethod: 'exists' === $assertion->getOperator() ? 'assertTrue' : 'assertFalse',
            assertionMessage: $this->assertionMessageFactory->create(
                assertion: $assertion,
                expected: $expected,
                examined: $examined,
            ),
            expected: null,
            examined: $examined,
        );
    }
}
