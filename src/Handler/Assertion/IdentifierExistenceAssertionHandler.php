<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSource\Block\TryCatch\CatchBlock;
use webignition\BasilCompilableSource\Block\TryCatch\TryBlock;
use webignition\BasilCompilableSource\Block\TryCatch\TryCatchBlock;
use webignition\BasilCompilableSource\Body\Body;
use webignition\BasilCompilableSource\Body\BodyInterface;
use webignition\BasilCompilableSource\ClassName;
use webignition\BasilCompilableSource\Expression\CatchExpression;
use webignition\BasilCompilableSource\Expression\ComparisonExpression;
use webignition\BasilCompilableSource\Expression\EncapsulatedExpression;
use webignition\BasilCompilableSource\Expression\ExpressionInterface;
use webignition\BasilCompilableSource\Expression\LiteralExpression;
use webignition\BasilCompilableSource\Expression\ObjectPropertyAccessExpression;
use webignition\BasilCompilableSource\Factory\ArgumentFactory;
use webignition\BasilCompilableSource\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\MethodInvocation\StaticObjectMethodInvocation;
use webignition\BasilCompilableSource\Statement\AssignmentStatement;
use webignition\BasilCompilableSource\Statement\Statement;
use webignition\BasilCompilableSource\StaticObject;
use webignition\BasilCompilableSource\TypeDeclaration\ObjectTypeDeclaration;
use webignition\BasilCompilableSource\TypeDeclaration\ObjectTypeDeclarationCollection;
use webignition\BasilCompilableSource\VariableDependency;
use webignition\BasilCompilableSource\VariableName;
use webignition\BasilCompilableSourceFactory\AssertionMethodInvocationFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\ElementIdentifierCallFactory;
use webignition\BasilCompilableSourceFactory\ElementIdentifierSerializer;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\DomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilDomIdentifierFactory\Factory as DomIdentifierFactory;
use webignition\BasilModels\Action\ActionInterface;
use webignition\BasilModels\Assertion\Assertion;
use webignition\BasilModels\Assertion\AssertionInterface;
use webignition\BasilModels\Assertion\DerivedValueOperationAssertion;
use webignition\DomElementIdentifier\AttributeIdentifierInterface;
use webignition\DomElementIdentifier\ElementIdentifier;
use webignition\DomElementIdentifier\ElementIdentifierInterface;
use webignition\SymfonyDomCrawlerNavigator\Exception\InvalidLocatorException;

class IdentifierExistenceAssertionHandler extends AbstractAssertionHandler
{
    public const ASSERT_TRUE_METHOD = 'assertTrue';
    public const ASSERT_FALSE_METHOD = 'assertFalse';

    private DomCrawlerNavigatorCallFactory $domCrawlerNavigatorCallFactory;
    private DomIdentifierFactory $domIdentifierFactory;
    private DomIdentifierHandler $domIdentifierHandler;
    private ElementIdentifierCallFactory $elementIdentifierCallFactory;
    private ElementIdentifierSerializer $elementIdentifierSerializer;
    private ArgumentFactory $argumentFactory;

    private const OPERATOR_TO_ASSERTION_TEMPLATE_MAP = [
        'exists' => self::ASSERT_TRUE_METHOD,
        'not-exists' => self::ASSERT_FALSE_METHOD,
    ];

    public function __construct(
        AssertionMethodInvocationFactory $assertionMethodInvocationFactory,
        DomCrawlerNavigatorCallFactory $domCrawlerNavigatorCallFactory,
        DomIdentifierFactory $domIdentifierFactory,
        DomIdentifierHandler $domIdentifierHandler,
        ElementIdentifierCallFactory $elementIdentifierCallFactory,
        ElementIdentifierSerializer $elementIdentifierSerializer,
        ArgumentFactory $argumentFactory
    ) {
        parent::__construct($assertionMethodInvocationFactory);

        $this->domCrawlerNavigatorCallFactory = $domCrawlerNavigatorCallFactory;
        $this->domIdentifierFactory = $domIdentifierFactory;
        $this->domIdentifierHandler = $domIdentifierHandler;
        $this->elementIdentifierCallFactory = $elementIdentifierCallFactory;
        $this->elementIdentifierSerializer = $elementIdentifierSerializer;
        $this->argumentFactory = $argumentFactory;
    }

    public static function createHandler(): self
    {
        return new IdentifierExistenceAssertionHandler(
            AssertionMethodInvocationFactory::createFactory(),
            DomCrawlerNavigatorCallFactory::createFactory(),
            DomIdentifierFactory::createFactory(),
            DomIdentifierHandler::createHandler(),
            ElementIdentifierCallFactory::createFactory(),
            ElementIdentifierSerializer::createSerializer(),
            ArgumentFactory::createFactory()
        );
    }

    protected function getOperationToAssertionTemplateMap(): array
    {
        return self::OPERATOR_TO_ASSERTION_TEMPLATE_MAP;
    }

    /**
     * @param AssertionInterface $assertion
     *
     * @return BodyInterface
     *
     * @throws UnsupportedContentException
     */
    public function handle(AssertionInterface $assertion): BodyInterface
    {
        $identifier = $assertion->getIdentifier();

        $assertionStatement = $this->createAssertionStatement($assertion, [
            $this->createGetBooleanExaminedValueInvocation()
        ]);

        $domIdentifier = $this->domIdentifierFactory->createFromIdentifierString($identifier);
        if (null === $domIdentifier) {
            throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
        }

        $serializedElementIdentifier = $this->elementIdentifierSerializer->serialize($domIdentifier);
        $elementIdentifierExpression = $this->elementIdentifierCallFactory->createConstructorCall(
            $serializedElementIdentifier
        );

        $examinedElementIdentifierPlaceholder = new ObjectPropertyAccessExpression(
            new VariableDependency(VariableNames::PHPUNIT_TEST_CASE),
            'examinedElementIdentifier'
        );

        $domNavigatorCrawlerCall = $this->createDomCrawlerNavigatorCall(
            $domIdentifier,
            $assertion,
            $examinedElementIdentifierPlaceholder
        );

        $elementSetBooleanExaminedValueInvocation = $this->createSetBooleanExaminedValueInvocation(
            [
                $domNavigatorCrawlerCall
            ],
            ObjectMethodInvocation::ARGUMENT_FORMAT_STACKED
        );

        if (!$domIdentifier instanceof AttributeIdentifierInterface) {
            return new Body([
                new AssignmentStatement(
                    $examinedElementIdentifierPlaceholder,
                    $elementIdentifierExpression
                ),
                $this->createNavigatorHasCallTryCatchBlock($elementSetBooleanExaminedValueInvocation),
                $assertionStatement,
            ]);
        }

        $elementIdentifierString = (string) ElementIdentifier::fromAttributeIdentifier($domIdentifier);
        $elementExistsAssertion = new Assertion(
            $elementIdentifierString . ' exists',
            $elementIdentifierString,
            'exists'
        );

        $attributeNullComparisonExpression = new ComparisonExpression(
            $this->domIdentifierHandler->handleAttributeValue(
                $this->elementIdentifierSerializer->serialize($domIdentifier),
                $domIdentifier->getAttributeName()
            ),
            new LiteralExpression('null'),
            '??'
        );

        $attributeSetBooleanExaminedValueInvocation = $this->createSetBooleanExaminedValueInvocation([
            new ComparisonExpression(
                new EncapsulatedExpression($attributeNullComparisonExpression),
                new LiteralExpression('null'),
                '!=='
            ),
        ]);

        return new Body([
            new AssignmentStatement(
                $examinedElementIdentifierPlaceholder,
                $elementIdentifierExpression
            ),
            $this->createNavigatorHasCallTryCatchBlock($elementSetBooleanExaminedValueInvocation),
            $this->createAssertionStatement($elementExistsAssertion, [
                $this->createGetBooleanExaminedValueInvocation()
            ]),
            new Statement($attributeSetBooleanExaminedValueInvocation),
            $assertionStatement,
        ]);
    }

    /**
     * @param ExpressionInterface[] $arguments
     * @param string $argumentFormat
     *
     * @return ExpressionInterface
     */
    private function createSetBooleanExaminedValueInvocation(
        array $arguments,
        string $argumentFormat = ObjectMethodInvocation::ARGUMENT_FORMAT_INLINE
    ): ExpressionInterface {
        return $this->createPhpUnitTestCaseObjectMethodInvocation(
            'setBooleanExaminedValue',
            $arguments,
            $argumentFormat
        );
    }

    private function createGetBooleanExaminedValueInvocation(): ExpressionInterface
    {
        return $this->createPhpUnitTestCaseObjectMethodInvocation('getBooleanExaminedValue');
    }

    private function createDomCrawlerNavigatorCall(
        ElementIdentifierInterface $domIdentifier,
        AssertionInterface $assertion,
        ObjectPropertyAccessExpression $expression
    ): ExpressionInterface {
        $isAttributeIdentifier = $domIdentifier instanceof AttributeIdentifierInterface;
        $isDerivedFromInteractionAction = false;

        if ($assertion instanceof DerivedValueOperationAssertion) {
            $sourceStatement = $assertion->getSourceStatement();

            $isDerivedFromInteractionAction =
                $sourceStatement instanceof ActionInterface && $sourceStatement->isInteraction();
        }

        return $isAttributeIdentifier || $isDerivedFromInteractionAction
                ? $this->domCrawlerNavigatorCallFactory->createHasOneCall($expression)
                : $this->domCrawlerNavigatorCallFactory->createHasCall($expression);
    }

    private function createNavigatorHasCallTryCatchBlock(
        ExpressionInterface $elementSetBooleanExaminedValueInvocation
    ): TryCatchBlock {
        return new TryCatchBlock(
            new TryBlock(
                Body::createFromExpressions([$elementSetBooleanExaminedValueInvocation])
            ),
            new CatchBlock(
                new CatchExpression(
                    new ObjectTypeDeclarationCollection([
                        new ObjectTypeDeclaration(new ClassName(InvalidLocatorException::class))
                    ])
                ),
                Body::createFromExpressions([
                    new StaticObjectMethodInvocation(
                        new StaticObject('self'),
                        'setLastException',
                        [
                            new VariableName('exception')
                        ]
                    ),
                    new ObjectMethodInvocation(
                        new VariableDependency(VariableNames::PHPUNIT_TEST_CASE),
                        'fail',
                        $this->argumentFactory->create('Invalid locator')
                    )
                ])
            )
        );
    }
}
