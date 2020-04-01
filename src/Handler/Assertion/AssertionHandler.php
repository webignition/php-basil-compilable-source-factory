<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSource\Block\CodeBlock;
use webignition\BasilCompilableSource\Block\CodeBlockInterface;
use webignition\BasilCompilableSource\Line\ClosureExpression;
use webignition\BasilCompilableSource\Line\ComparisonExpression;
use webignition\BasilCompilableSource\Line\ExpressionInterface;
use webignition\BasilCompilableSource\Line\LiteralExpression;
use webignition\BasilCompilableSource\Line\ObjectPropertyAccessExpression;
use webignition\BasilCompilableSource\Line\Statement\AssignmentStatement;
use webignition\BasilCompilableSource\Line\Statement\ObjectPropertyAssignmentStatement;
use webignition\BasilCompilableSource\Line\Statement\Statement;
use webignition\BasilCompilableSource\Line\Statement\StatementInterface;
use webignition\BasilCompilableSource\VariablePlaceholder;
use webignition\BasilCompilableSourceFactory\AccessorDefaultValueFactory;
use webignition\BasilCompilableSourceFactory\AssertionFailureMessageFactory;
use webignition\BasilCompilableSourceFactory\AssertionMethodInvocationFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\ElementIdentifierCallFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStatementException;
use webignition\BasilCompilableSourceFactory\Handler\DomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilableSourceFactory\Model\DomIdentifierValue;
use webignition\BasilCompilableSourceFactory\ValueTypeIdentifier;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilDomIdentifierFactory\Factory as DomIdentifierFactory;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Assertion\Assertion;
use webignition\BasilModels\Assertion\AssertionInterface;
use webignition\BasilModels\Assertion\ComparisonAssertionInterface;
use webignition\DomElementIdentifier\AttributeIdentifierInterface;
use webignition\DomElementIdentifier\ElementIdentifier;

class AssertionHandler
{
    public const ASSERT_EQUALS_METHOD = 'assertEquals';
    public const ASSERT_NOT_EQUALS_METHOD = 'assertNotEquals';
    public const ASSERT_STRING_CONTAINS_STRING_METHOD = 'assertStringContainsString';
    public const ASSERT_STRING_NOT_CONTAINS_STRING_METHOD = 'assertStringNotContainsString';
    public const ASSERT_MATCHES_METHOD = 'assertRegExp';
    public const ASSERT_TRUE_METHOD = 'assertTrue';
    public const ASSERT_FALSE_METHOD = 'assertFalse';

    private const HANDLE_EXISTENCE_AS_ELEMENT = 1;
    private const HANDLE_EXISTENCE_AS_COLLECTION = 2;

    private $accessorDefaultValueFactory;
    private $assertionFailureMessageFactory;
    private $assertionMethodInvocationFactory;
    private $domCrawlerNavigatorCallFactory;
    private $domIdentifierFactory;
    private $domIdentifierHandler;
    private $identifierTypeAnalyser;
    private $scalarValueHandler;
    private $valueTypeIdentifier;
    private $elementIdentifierCallFactory;

    private const COMPARISON_TO_ASSERTION_TEMPLATE_MAP = [
        'includes' => self::ASSERT_STRING_CONTAINS_STRING_METHOD,
        'excludes' => self::ASSERT_STRING_NOT_CONTAINS_STRING_METHOD,
        'is' => self::ASSERT_EQUALS_METHOD,
        'is-not' => self::ASSERT_NOT_EQUALS_METHOD,
        'matches' => self::ASSERT_MATCHES_METHOD,
        'exists' => self::ASSERT_TRUE_METHOD,
        'not-exists' => self::ASSERT_FALSE_METHOD,
    ];

    /**
     * @var string[]
     */
    private $methodsWithStringArguments = [
        self::ASSERT_STRING_CONTAINS_STRING_METHOD,
        self::ASSERT_STRING_NOT_CONTAINS_STRING_METHOD,
    ];

    public function __construct(
        AccessorDefaultValueFactory $accessorDefaultValueFactory,
        AssertionFailureMessageFactory $assertionFailureMessageFactory,
        AssertionMethodInvocationFactory $assertionMethodInvocationFactory,
        DomCrawlerNavigatorCallFactory $domCrawlerNavigatorCallFactory,
        DomIdentifierFactory $domIdentifierFactory,
        DomIdentifierHandler $domIdentifierHandler,
        IdentifierTypeAnalyser $identifierTypeAnalyser,
        ScalarValueHandler $scalarValueHandler,
        ValueTypeIdentifier $valueTypeIdentifier,
        ElementIdentifierCallFactory $elementIdentifierCallFactory
    ) {
        $this->accessorDefaultValueFactory = $accessorDefaultValueFactory;
        $this->assertionFailureMessageFactory = $assertionFailureMessageFactory;
        $this->assertionMethodInvocationFactory = $assertionMethodInvocationFactory;
        $this->domCrawlerNavigatorCallFactory = $domCrawlerNavigatorCallFactory;
        $this->domIdentifierFactory = $domIdentifierFactory;
        $this->domIdentifierHandler = $domIdentifierHandler;
        $this->identifierTypeAnalyser = $identifierTypeAnalyser;
        $this->scalarValueHandler = $scalarValueHandler;
        $this->valueTypeIdentifier = $valueTypeIdentifier;
        $this->elementIdentifierCallFactory = $elementIdentifierCallFactory;
    }

    public static function createHandler(): AssertionHandler
    {
        return new AssertionHandler(
            AccessorDefaultValueFactory::createFactory(),
            AssertionFailureMessageFactory::createFactory(),
            AssertionMethodInvocationFactory::createFactory(),
            DomCrawlerNavigatorCallFactory::createFactory(),
            DomIdentifierFactory::createFactory(),
            DomIdentifierHandler::createHandler(),
            IdentifierTypeAnalyser::create(),
            ScalarValueHandler::createHandler(),
            new ValueTypeIdentifier(),
            ElementIdentifierCallFactory::createFactory()
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
            if ($this->isComparisonAssertion($assertion) && $assertion instanceof ComparisonAssertionInterface) {
                return $this->handleComparisonAssertion($assertion);
            }

            if ($this->isExistenceAssertion($assertion)) {
                return $this->handleExistenceAssertion($assertion);
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
    public function handleExistenceAssertionAsElement(AssertionInterface $assertion): CodeBlockInterface
    {
        return $this->handleExistenceAssertion($assertion, self::HANDLE_EXISTENCE_AS_ELEMENT);
    }

    /**
     * @param AssertionInterface $assertion
     *
     * @return CodeBlockInterface
     *
     * @throws UnsupportedContentException
     */
    public function handleExistenceAssertionAsCollection(AssertionInterface $assertion): CodeBlockInterface
    {
        return $this->handleExistenceAssertion($assertion, self::HANDLE_EXISTENCE_AS_COLLECTION);
    }

    /**
     * @param AssertionInterface $assertion
     *
     * @param int $handleAs
     * @return CodeBlockInterface
     *
     * @throws UnsupportedContentException
     */
    private function handleExistenceAssertion(
        AssertionInterface $assertion,
        ?int $handleAs = null
    ): CodeBlockInterface {
        $identifier = $assertion->getIdentifier();

        $valuePlaceholder = new ObjectPropertyAccessExpression(
            VariablePlaceholder::createDependency(VariableNames::PHPUNIT_TEST_CASE),
            'examinedValue'
        );

        if ($this->valueTypeIdentifier->isScalarValue($identifier)) {
            return new CodeBlock([
                new ObjectPropertyAssignmentStatement(
                    $valuePlaceholder,
                    new ComparisonExpression(
                        $this->scalarValueHandler->handle($assertion->getIdentifier()),
                        new LiteralExpression('null'),
                        '??'
                    )
                ),
                new ObjectPropertyAssignmentStatement(
                    $valuePlaceholder,
                    new ComparisonExpression(
                        $valuePlaceholder,
                        new LiteralExpression('null'),
                        '!=='
                    )
                ),
                $this->createAssertionStatement($assertion, [$valuePlaceholder]),
            ]);
        }

        if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($identifier)) {
            $domIdentifier = $this->domIdentifierFactory->createFromIdentifierString($identifier);
            if (null === $domIdentifier) {
                throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
            }

            $elementIdentifierExpression = $this->elementIdentifierCallFactory->createConstructorCall($domIdentifier);
            $examinedElementIdentifierPlaceholder = new ObjectPropertyAccessExpression(
                VariablePlaceholder::createDependency(VariableNames::PHPUNIT_TEST_CASE),
                'examinedElementIdentifier'
            );

            if (!$domIdentifier instanceof AttributeIdentifierInterface) {
                $domNavigatorCrawlerCall = self::HANDLE_EXISTENCE_AS_ELEMENT === $handleAs
                    ? $this->domCrawlerNavigatorCallFactory->createHasOneCall($examinedElementIdentifierPlaceholder)
                    : $this->domCrawlerNavigatorCallFactory->createHasCall($examinedElementIdentifierPlaceholder);

                return new CodeBlock([
                    new AssignmentStatement(
                        $examinedElementIdentifierPlaceholder,
                        $elementIdentifierExpression
                    ),
                    new AssignmentStatement(
                        $valuePlaceholder,
                        $domNavigatorCrawlerCall
                    ),
                    $this->createAssertionStatement($assertion, [$valuePlaceholder]),
                ]);
            }

            $elementIdentifierString = (string) ElementIdentifier::fromAttributeIdentifier($domIdentifier);
            $elementExistsAssertion = new Assertion(
                $elementIdentifierString . ' exists',
                $elementIdentifierString,
                'exists'
            );

            $domNavigatorCrawlerCall =
                $this->domCrawlerNavigatorCallFactory->createHasOneCall($examinedElementIdentifierPlaceholder);

            return new CodeBlock([
                new AssignmentStatement(
                    $examinedElementIdentifierPlaceholder,
                    $elementIdentifierExpression
                ),
                new AssignmentStatement(
                    $valuePlaceholder,
                    $domNavigatorCrawlerCall
                ),
                $this->createAssertionStatement($elementExistsAssertion, [$valuePlaceholder]),
                new AssignmentStatement(
                    $valuePlaceholder,
                    $this->domIdentifierHandler->handle(new DomIdentifierValue($domIdentifier))
                ),
                new AssignmentStatement(
                    $valuePlaceholder,
                    new ComparisonExpression(
                        $valuePlaceholder,
                        new LiteralExpression('null'),
                        '!=='
                    )
                ),
                $this->createAssertionStatement($assertion, [$valuePlaceholder]),
            ]);
        }

        throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
    }

    /**
     * @param ComparisonAssertionInterface $assertion
     *
     * @return CodeBlockInterface
     *
     * @throws UnsupportedContentException
     */
    private function handleComparisonAssertion(ComparisonAssertionInterface $assertion): CodeBlockInterface
    {
        $assertionMethod = self::COMPARISON_TO_ASSERTION_TEMPLATE_MAP[$assertion->getComparison()];

        $isStringArgumentAssertionMethod = in_array($assertionMethod, $this->methodsWithStringArguments);

        $examinedPlaceholder = new ObjectPropertyAccessExpression(
            VariablePlaceholder::createDependency(VariableNames::PHPUNIT_TEST_CASE),
            'examinedValue',
            $isStringArgumentAssertionMethod ? 'string' : null
        );

        $expectedPlaceholder = new ObjectPropertyAccessExpression(
            VariablePlaceholder::createDependency(VariableNames::PHPUNIT_TEST_CASE),
            'expectedValue',
            $isStringArgumentAssertionMethod ? 'string' : null
        );

        $examinedAccessor = $this->createValueAccessor($assertion->getIdentifier());
        $expectedAccessor = $this->createValueAccessor($assertion->getValue());

        $examinedValueAssignment = new ObjectPropertyAssignmentStatement($examinedPlaceholder, $examinedAccessor);
        $expectedValueAssignment = new ObjectPropertyAssignmentStatement($expectedPlaceholder, $expectedAccessor);

        return new CodeBlock([
            $expectedValueAssignment,
            $examinedValueAssignment,
            $this->createAssertionStatement($assertion, [$expectedPlaceholder, $examinedPlaceholder]),
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
        if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($value)) {
            $examinedValueDomIdentifier = $this->domIdentifierFactory->createFromIdentifierString($value);
            if (null === $examinedValueDomIdentifier) {
                throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $value);
            }

            $accessor = $this->domIdentifierHandler->handle(
                new DomIdentifierValue($examinedValueDomIdentifier)
            );
        } else {
            $accessor = $this->scalarValueHandler->handle($value);
        }

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
                self::COMPARISON_TO_ASSERTION_TEMPLATE_MAP[$assertion->getComparison()],
                $arguments,
                $this->assertionFailureMessageFactory->createForAssertion($assertion)
            )
        );
    }

    private function isComparisonAssertion(AssertionInterface $assertion): bool
    {
        return in_array($assertion->getComparison(), [
            'includes',
            'excludes',
            'is',
            'is-not',
            'matches',
        ]);
    }

    private function isExistenceAssertion(AssertionInterface $assertion): bool
    {
        return in_array($assertion->getComparison(), ['exists', 'not-exists']);
    }
}
