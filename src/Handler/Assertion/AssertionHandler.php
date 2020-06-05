<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSource\Block\CodeBlock;
use webignition\BasilCompilableSource\Block\CodeBlockInterface;
use webignition\BasilCompilableSource\Line\CastExpression;
use webignition\BasilCompilableSource\Line\ClosureExpression;
use webignition\BasilCompilableSource\Line\ComparisonExpression;
use webignition\BasilCompilableSource\Line\EncapsulatedExpression;
use webignition\BasilCompilableSource\Line\ExpressionInterface;
use webignition\BasilCompilableSource\Line\LiteralExpression;
use webignition\BasilCompilableSource\Line\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSource\Line\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\Line\ObjectPropertyAccessExpression;
use webignition\BasilCompilableSource\Line\Statement\AssignmentStatement;
use webignition\BasilCompilableSource\Line\Statement\Statement;
use webignition\BasilCompilableSource\Line\Statement\StatementInterface;
use webignition\BasilCompilableSource\VariableDependency;
use webignition\BasilCompilableSourceFactory\AccessorDefaultValueFactory;
use webignition\BasilCompilableSourceFactory\AssertionMethodInvocationFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\ElementIdentifierCallFactory;
use webignition\BasilCompilableSourceFactory\ElementIdentifierSerializer;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStatementException;
use webignition\BasilCompilableSourceFactory\Handler\DomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilableSourceFactory\ValueAccessorFactory;
use webignition\BasilCompilableSourceFactory\ValueTypeIdentifier;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilDomIdentifierFactory\Factory as DomIdentifierFactory;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Action\ActionInterface;
use webignition\BasilModels\Assertion\Assertion;
use webignition\BasilModels\Assertion\AssertionInterface;
use webignition\BasilModels\Assertion\DerivedValueOperationAssertion;
use webignition\DomElementIdentifier\AttributeIdentifierInterface;
use webignition\DomElementIdentifier\ElementIdentifier;
use webignition\DomElementIdentifier\ElementIdentifierInterface;

class AssertionHandler
{
    public const ASSERT_EQUALS_METHOD = 'assertEquals';
    public const ASSERT_NOT_EQUALS_METHOD = 'assertNotEquals';
    public const ASSERT_STRING_CONTAINS_STRING_METHOD = 'assertStringContainsString';
    public const ASSERT_STRING_NOT_CONTAINS_STRING_METHOD = 'assertStringNotContainsString';
    public const ASSERT_MATCHES_METHOD = 'assertRegExp';
    public const ASSERT_TRUE_METHOD = 'assertTrue';
    public const ASSERT_FALSE_METHOD = 'assertFalse';

    private AccessorDefaultValueFactory $accessorDefaultValueFactory;
    private AssertionMethodInvocationFactory $assertionMethodInvocationFactory;
    private DomCrawlerNavigatorCallFactory $domCrawlerNavigatorCallFactory;
    private DomIdentifierFactory $domIdentifierFactory;
    private DomIdentifierHandler $domIdentifierHandler;
    private IdentifierTypeAnalyser $identifierTypeAnalyser;
    private ScalarValueHandler $scalarValueHandler;
    private ValueTypeIdentifier $valueTypeIdentifier;
    private ElementIdentifierCallFactory $elementIdentifierCallFactory;
    private ElementIdentifierSerializer $elementIdentifierSerializer;
    private ValueAccessorFactory $valueAccessorFactory;

    private const OPERATOR_TO_ASSERTION_TEMPLATE_MAP = [
        'includes' => self::ASSERT_STRING_CONTAINS_STRING_METHOD,
        'excludes' => self::ASSERT_STRING_NOT_CONTAINS_STRING_METHOD,
        'is' => self::ASSERT_EQUALS_METHOD,
        'is-not' => self::ASSERT_NOT_EQUALS_METHOD,
        'matches' => self::ASSERT_MATCHES_METHOD,
        'exists' => self::ASSERT_TRUE_METHOD,
        'not-exists' => self::ASSERT_FALSE_METHOD,
        'is-regexp' => self::ASSERT_FALSE_METHOD,
    ];

    /**
     * @var string[]
     */
    private array $methodsWithStringArguments = [
        self::ASSERT_STRING_CONTAINS_STRING_METHOD,
        self::ASSERT_STRING_NOT_CONTAINS_STRING_METHOD,
    ];

    public function __construct(
        AccessorDefaultValueFactory $accessorDefaultValueFactory,
        AssertionMethodInvocationFactory $assertionMethodInvocationFactory,
        DomCrawlerNavigatorCallFactory $domCrawlerNavigatorCallFactory,
        DomIdentifierFactory $domIdentifierFactory,
        DomIdentifierHandler $domIdentifierHandler,
        IdentifierTypeAnalyser $identifierTypeAnalyser,
        ScalarValueHandler $scalarValueHandler,
        ValueTypeIdentifier $valueTypeIdentifier,
        ElementIdentifierCallFactory $elementIdentifierCallFactory,
        ElementIdentifierSerializer $elementIdentifierSerializer,
        ValueAccessorFactory $valueAccessorFactory
    ) {
        $this->accessorDefaultValueFactory = $accessorDefaultValueFactory;
        $this->assertionMethodInvocationFactory = $assertionMethodInvocationFactory;
        $this->domCrawlerNavigatorCallFactory = $domCrawlerNavigatorCallFactory;
        $this->domIdentifierFactory = $domIdentifierFactory;
        $this->domIdentifierHandler = $domIdentifierHandler;
        $this->identifierTypeAnalyser = $identifierTypeAnalyser;
        $this->scalarValueHandler = $scalarValueHandler;
        $this->valueTypeIdentifier = $valueTypeIdentifier;
        $this->elementIdentifierCallFactory = $elementIdentifierCallFactory;
        $this->elementIdentifierSerializer = $elementIdentifierSerializer;
        $this->valueAccessorFactory = $valueAccessorFactory;
    }

    public static function createHandler(): AssertionHandler
    {
        return new AssertionHandler(
            AccessorDefaultValueFactory::createFactory(),
            AssertionMethodInvocationFactory::createFactory(),
            DomCrawlerNavigatorCallFactory::createFactory(),
            DomIdentifierFactory::createFactory(),
            DomIdentifierHandler::createHandler(),
            IdentifierTypeAnalyser::create(),
            ScalarValueHandler::createHandler(),
            new ValueTypeIdentifier(),
            ElementIdentifierCallFactory::createFactory(),
            ElementIdentifierSerializer::createSerializer(),
            ValueAccessorFactory::createFactory()
        );
    }

    /**
     * @param AssertionInterface $assertion
     *
     * @return CodeBlockInterface
     *
     * @throws UnsupportedStatementException
     */
    public function handle(AssertionInterface $assertion): CodeBlockInterface
    {
        try {
            if ($assertion->isComparison()) {
                return $this->handleComparisonAssertion($assertion);
            } else {
                if (in_array($assertion->getOperator(), ['exists', 'not-exists'])) {
                    return $this->handleExistenceAssertion($assertion);
                }

                if ('is-regexp' === $assertion->getOperator()) {
                    return $this->handleIsRegExpAssertion($assertion);
                }
            }
        } catch (UnsupportedContentException $previous) {
            throw new UnsupportedStatementException($assertion, $previous);
        }

        throw new UnsupportedStatementException($assertion);
    }

    /**
     * @param AssertionInterface $assertion
     *
     * @return CodeBlockInterface
     *
     * @throws UnsupportedContentException
     */
    private function handleExistenceAssertion(AssertionInterface $assertion): CodeBlockInterface
    {
        $identifier = $assertion->getIdentifier();

        $assertionStatement = $this->createAssertionStatement($assertion, [
            $this->createGetBooleanExaminedValueInvocation()
        ]);

        if ($this->valueTypeIdentifier->isScalarValue($identifier)) {
            return $this->handleScalarExistenceAssertion($assertion);
        }

        if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($identifier)) {
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

            $domNavigatorCrawlerCall = $this->createExistenceOperationDomCrawlerNavigatorCall(
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
                return new CodeBlock([
                    new AssignmentStatement(
                        $examinedElementIdentifierPlaceholder,
                        $elementIdentifierExpression
                    ),
                    new Statement($elementSetBooleanExaminedValueInvocation),
                    $assertionStatement,
                ]);
            }

            $elementIdentifierString = (string) ElementIdentifier::fromAttributeIdentifier($domIdentifier);
            $elementExistsAssertion = new Assertion(
                $elementIdentifierString . ' exists',
                $elementIdentifierString,
                'exists'
            );

            $attributeNullComparisonExpression = $this->createNullComparisonExpression(
                $this->domIdentifierHandler->handleAttributeValue(
                    $this->elementIdentifierSerializer->serialize($domIdentifier),
                    $domIdentifier->getAttributeName()
                )
            );

            $attributeSetBooleanExaminedValueInvocation = $this->createSetBooleanExaminedValueInvocation([
                new ComparisonExpression(
                    new EncapsulatedExpression($attributeNullComparisonExpression),
                    new LiteralExpression('null'),
                    '!=='
                ),
            ]);

            return new CodeBlock([
                new AssignmentStatement(
                    $examinedElementIdentifierPlaceholder,
                    $elementIdentifierExpression
                ),
                new Statement($elementSetBooleanExaminedValueInvocation),
                $this->createAssertionStatement($elementExistsAssertion, [
                    $this->createGetBooleanExaminedValueInvocation()
                ]),
                new Statement($attributeSetBooleanExaminedValueInvocation),
                $assertionStatement,
            ]);
        }

        throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
    }

    /**
     * @param AssertionInterface $assertion
     *
     * @return CodeBlockInterface
     *
     * @throws UnsupportedContentException
     */
    private function handleScalarExistenceAssertion(AssertionInterface $assertion): CodeBlockInterface
    {
        $nullComparisonExpression = $this->createNullComparisonExpression(
            $this->scalarValueHandler->handle($assertion->getIdentifier())
        );

        $setBooleanExaminedValueInvocation = $this->createSetBooleanExaminedValueInvocation([
            new ComparisonExpression(
                new EncapsulatedExpression($nullComparisonExpression),
                new LiteralExpression('null'),
                '!=='
            ),
        ]);

        $assertionStatement = $this->createAssertionStatement($assertion, [
            $this->createGetBooleanExaminedValueInvocation()
        ]);

        return new CodeBlock([
            new Statement($setBooleanExaminedValueInvocation),
            $assertionStatement,
        ]);
    }

    private function createNullComparisonExpression(ExpressionInterface $leftHandSide): ExpressionInterface
    {
        return new ComparisonExpression($leftHandSide, new LiteralExpression('null'), '??');
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

    /**
     * @param ExpressionInterface[] $arguments
     * @param string $argumentFormat
     *
     * @return ExpressionInterface
     */
    private function createSetBooleanExpectedValueInvocation(
        array $arguments,
        string $argumentFormat = ObjectMethodInvocation::ARGUMENT_FORMAT_INLINE
    ): ExpressionInterface {
        return $this->createPhpUnitTestCaseObjectMethodInvocation(
            'setBooleanExpectedValue',
            $arguments,
            $argumentFormat
        );
    }

    private function createGetBooleanExaminedValueInvocation(): ExpressionInterface
    {
        return $this->createPhpUnitTestCaseObjectMethodInvocation('getBooleanExaminedValue');
    }

    private function createGetBooleanExpectedValueInvocation(): ExpressionInterface
    {
        return $this->createPhpUnitTestCaseObjectMethodInvocation('getBooleanExpectedValue');
    }

    /**
     * @param ExpressionInterface[] $arguments
     *
     * @return ExpressionInterface
     */
    private function createSetExaminedValueInvocation(array $arguments): ExpressionInterface
    {
        return $this->createPhpUnitTestCaseObjectMethodInvocation(
            'setExaminedValue',
            $arguments
        );
    }

    private function createGetExaminedValueInvocation(): ExpressionInterface
    {
        return $this->createPhpUnitTestCaseObjectMethodInvocation('getExaminedValue');
    }

    private function createGetExpectedValueInvocation(): ExpressionInterface
    {
        return $this->createPhpUnitTestCaseObjectMethodInvocation('getExpectedValue');
    }

    /**
     * @param ExpressionInterface[] $arguments
     *
     * @return ExpressionInterface
     */
    private function createSetExpectedValueInvocation(array $arguments): ExpressionInterface
    {
        return $this->createPhpUnitTestCaseObjectMethodInvocation(
            'setExpectedValue',
            $arguments
        );
    }

    /**
     * @param string $methodName
     * @param ExpressionInterface[] $arguments
     * @param string $argumentFormat
     *
     * @return ExpressionInterface
     */
    private function createPhpUnitTestCaseObjectMethodInvocation(
        string $methodName,
        array $arguments = [],
        string $argumentFormat = ObjectMethodInvocation::ARGUMENT_FORMAT_INLINE
    ): ExpressionInterface {
        return new ObjectMethodInvocation(
            new VariableDependency(VariableNames::PHPUNIT_TEST_CASE),
            $methodName,
            $arguments,
            $argumentFormat
        );
    }

    private function createExistenceOperationDomCrawlerNavigatorCall(
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

    /**
     * @param AssertionInterface $assertion
     *
     * @return CodeBlockInterface
     *
     * @throws UnsupportedContentException
     */
    private function handleComparisonAssertion(AssertionInterface $assertion): CodeBlockInterface
    {
        $assertionMethod = self::OPERATOR_TO_ASSERTION_TEMPLATE_MAP[$assertion->getOperator()];

        $examinedAccessor = $this->createValueAccessor($assertion->getIdentifier());
        $expectedAccessor = $this->createValueAccessor($assertion->getValue());

        $assertionArguments = [
            $this->createGetExpectedValueInvocation(),
            $this->createGetExaminedValueInvocation(),
        ];

        $isStringArgumentAssertionMethod = in_array($assertionMethod, $this->methodsWithStringArguments);
        if ($isStringArgumentAssertionMethod) {
            array_walk($assertionArguments, function (ExpressionInterface &$expression) {
                $expression = new CastExpression($expression, 'string');
            });
        }

        return new CodeBlock([
            new Statement($this->createSetExpectedValueInvocation([$expectedAccessor])),
            new Statement($this->createSetExaminedValueInvocation([$examinedAccessor])),
            $this->createAssertionStatement($assertion, $assertionArguments),
        ]);
    }

    /**
     * @param AssertionInterface $assertion
     *
     * @return CodeBlockInterface
     *
     * @throws UnsupportedContentException
     */
    private function handleIsRegExpAssertion(AssertionInterface $assertion): CodeBlockInterface
    {
        $identifier = $assertion->getIdentifier();

        if ($this->valueTypeIdentifier->isScalarValue($identifier)) {
            $examinedAccessor = new LiteralExpression($identifier);

            return $this->createIsRegExpAssertionCodeBlock($examinedAccessor, $assertion);
        }

        if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($identifier)) {
            $domIdentifier = $this->domIdentifierFactory->createFromIdentifierString($identifier);
            if (null === $domIdentifier) {
                throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
            }

            $examinedAccessor = $this->createValueAccessor($assertion->getIdentifier());

            return $this->createIsRegExpAssertionCodeBlock($examinedAccessor, $assertion);
        }

        throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
    }

    private function createIsRegExpAssertionCodeBlock(
        ExpressionInterface $examinedAccessor,
        AssertionInterface $assertion
    ): CodeBlockInterface {
        $pregMatchInvocation = new MethodInvocation(
            'preg_match',
            [
                $this->createGetExaminedValueInvocation(),
                new LiteralExpression('null'),
            ]
        );
        $pregMatchInvocation->enableErrorSuppression();

        $identityComparison = new ComparisonExpression(
            $pregMatchInvocation,
            new LiteralExpression('false'),
            '==='
        );

        return new CodeBlock([
            new Statement($this->createSetExaminedValueInvocation([
                $examinedAccessor
            ])),
            new Statement($this->createSetBooleanExpectedValueInvocation(
                [
                    $identityComparison
                ],
                MethodInvocation::ARGUMENT_FORMAT_STACKED
            )),
            $this->createAssertionStatement($assertion, [
                $this->createGetBooleanExpectedValueInvocation()
            ]),
        ]);
    }

    /**
     * @param string $value
     *
     * @return ExpressionInterface
     *
     * @throws UnsupportedContentException
     */
    private function createValueAccessor(string $value): ExpressionInterface
    {
        $accessor = $this->valueAccessorFactory->create($value);

        if (!$accessor instanceof ClosureExpression) {
            $defaultValue = $this->accessorDefaultValueFactory->createString($value) ?? 'null';

            $accessor = new ComparisonExpression(
                $accessor,
                new LiteralExpression($defaultValue),
                '??'
            );
        }

        return $accessor;
    }

    /**
     * @param AssertionInterface $assertion
     * @param ExpressionInterface[] $arguments
     *
     * @return StatementInterface
     */
    private function createAssertionStatement(AssertionInterface $assertion, array $arguments): StatementInterface
    {
        return new Statement(
            $this->assertionMethodInvocationFactory->create(
                self::OPERATOR_TO_ASSERTION_TEMPLATE_MAP[$assertion->getOperator()],
                $arguments
            )
        );
    }
}
