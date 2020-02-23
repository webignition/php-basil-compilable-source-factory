<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSource\Block\CodeBlock;
use webignition\BasilCompilableSource\Block\CodeBlockInterface;
use webignition\BasilCompilableSource\Line\ClosureExpression;
use webignition\BasilCompilableSource\Line\ComparisonExpression;
use webignition\BasilCompilableSource\Line\ExpressionInterface;
use webignition\BasilCompilableSource\Line\LiteralExpression;
use webignition\BasilCompilableSource\Line\Statement\AssignmentStatement;
use webignition\BasilCompilableSource\Line\Statement\Statement;
use webignition\BasilCompilableSource\Line\Statement\StatementInterface;
use webignition\BasilCompilableSource\VariablePlaceholder;
use webignition\BasilCompilableSourceFactory\AccessorDefaultValueFactory;
use webignition\BasilCompilableSourceFactory\AssertionFailureMessageFactory;
use webignition\BasilCompilableSourceFactory\AssertionMethodInvocationFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStatementException;
use webignition\BasilCompilableSourceFactory\Handler\DomIdentifierExistenceHandler;
use webignition\BasilCompilableSourceFactory\Handler\DomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilableSourceFactory\Model\DomIdentifierValue;
use webignition\BasilCompilableSourceFactory\ValueTypeIdentifier;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilDomIdentifierFactory\Factory as DomIdentifierFactory;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Assertion\AssertionInterface;
use webignition\BasilModels\Assertion\ComparisonAssertionInterface;
use webignition\DomElementIdentifier\AttributeIdentifierInterface;

class AssertionHandler
{
    public const ASSERT_EQUALS_METHOD = 'assertEquals';
    public const ASSERT_NOT_EQUALS_METHOD = 'assertNotEquals';
    public const ASSERT_STRING_CONTAINS_STRING_METHOD = 'assertStringContainsString';
    public const ASSERT_STRING_NOT_CONTAINS_STRING_METHOD = 'assertStringNotContainsString';
    public const ASSERT_MATCHES_METHOD = 'assertRegExp';

    private $accessorDefaultValueFactory;
    private $assertionFailureMessageFactory;
    private $assertionMethodInvocationFactory;
    private $domCrawlerNavigatorCallFactory;
    private $domIdentifierExistenceHandler;
    private $domIdentifierFactory;
    private $domIdentifierHandler;
    private $identifierTypeAnalyser;
    private $scalarValueHandler;
    private $valueTypeIdentifier;

    private const COMPARISON_TO_ASSERTION_TEMPLATE_MAP = [
        'includes' => self::ASSERT_STRING_CONTAINS_STRING_METHOD,
        'excludes' => self::ASSERT_STRING_NOT_CONTAINS_STRING_METHOD,
        'is' => self::ASSERT_EQUALS_METHOD,
        'is-not' => self::ASSERT_NOT_EQUALS_METHOD,
        'matches' => self::ASSERT_MATCHES_METHOD,
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
        DomIdentifierExistenceHandler $domIdentifierExistenceHandler,
        DomIdentifierFactory $domIdentifierFactory,
        DomIdentifierHandler $domIdentifierHandler,
        IdentifierTypeAnalyser $identifierTypeAnalyser,
        ScalarValueHandler $scalarValueHandler,
        ValueTypeIdentifier $valueTypeIdentifier
    ) {
        $this->accessorDefaultValueFactory = $accessorDefaultValueFactory;
        $this->assertionFailureMessageFactory = $assertionFailureMessageFactory;
        $this->assertionMethodInvocationFactory = $assertionMethodInvocationFactory;
        $this->domCrawlerNavigatorCallFactory = $domCrawlerNavigatorCallFactory;
        $this->domIdentifierExistenceHandler = $domIdentifierExistenceHandler;
        $this->domIdentifierFactory = $domIdentifierFactory;
        $this->domIdentifierHandler = $domIdentifierHandler;
        $this->identifierTypeAnalyser = $identifierTypeAnalyser;
        $this->scalarValueHandler = $scalarValueHandler;
        $this->valueTypeIdentifier = $valueTypeIdentifier;
    }

    public static function createHandler(): AssertionHandler
    {
        return new AssertionHandler(
            AccessorDefaultValueFactory::createFactory(),
            AssertionFailureMessageFactory::createFactory(),
            AssertionMethodInvocationFactory::createFactory(),
            DomCrawlerNavigatorCallFactory::createFactory(),
            DomIdentifierExistenceHandler::createHandler(),
            DomIdentifierFactory::createFactory(),
            DomIdentifierHandler::createHandler(),
            IdentifierTypeAnalyser::create(),
            ScalarValueHandler::createHandler(),
            new ValueTypeIdentifier()
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
    private function handleExistenceAssertion(AssertionInterface $assertion): CodeBlockInterface
    {
        $valuePlaceholder = VariablePlaceholder::createExport(VariableNames::EXAMINED_VALUE);
        $identifier = $assertion->getIdentifier();

        if ($this->valueTypeIdentifier->isScalarValue($identifier)) {
            return new CodeBlock([
                new AssignmentStatement(
                    $valuePlaceholder,
                    new ComparisonExpression(
                        $this->scalarValueHandler->handle($assertion->getIdentifier()),
                        new LiteralExpression('null'),
                        '??'
                    )
                ),
                new AssignmentStatement(
                    $valuePlaceholder,
                    new ComparisonExpression(
                        $valuePlaceholder,
                        new LiteralExpression('null'),
                        '!=='
                    )
                ),
                $this->createAssertionStatement($assertion, $valuePlaceholder),
            ]);
        }

        if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($identifier)) {
            $domIdentifier = $this->domIdentifierFactory->createFromIdentifierString($identifier);
            if (null === $domIdentifier) {
                throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
            }

            if (!$domIdentifier instanceof AttributeIdentifierInterface) {
                return new CodeBlock([
                    new AssignmentStatement(
                        $valuePlaceholder,
                        $this->domCrawlerNavigatorCallFactory->createHasCall($domIdentifier)
                    ),
                    $this->createAssertionStatement($assertion, $valuePlaceholder),
                ]);
            }

            return new CodeBlock([
                $this->domIdentifierExistenceHandler->createForElement(
                    $domIdentifier,
                    $this->assertionFailureMessageFactory->createForAssertion($assertion)
                ),
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
                $this->createAssertionStatement($assertion, $valuePlaceholder),
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
        $examinedValuePlaceholder = VariablePlaceholder::createExport(VariableNames::EXAMINED_VALUE);
        $expectedValuePlaceholder = VariablePlaceholder::createExport(VariableNames::EXPECTED_VALUE);

        $examinedValueAccessor = $this->createValueAccessor($assertion->getIdentifier());
        $expectedValueAccessor = $this->createValueAccessor($assertion->getValue());

        $examinedValueAssignment = new AssignmentStatement($examinedValuePlaceholder, $examinedValueAccessor);
        $expectedValueAssignment = new AssignmentStatement($expectedValuePlaceholder, $expectedValueAccessor);

        $assertionMethod = self::COMPARISON_TO_ASSERTION_TEMPLATE_MAP[$assertion->getComparison()];

        if (in_array($assertionMethod, $this->methodsWithStringArguments)) {
            $expectedValuePlaceholder = VariablePlaceholder::createExport(
                $expectedValuePlaceholder->getName(),
                'string'
            );

            $examinedValuePlaceholder = VariablePlaceholder::createExport(
                $examinedValuePlaceholder->getName(),
                'string'
            );
        }

        return new CodeBlock([
            $expectedValueAssignment,
            $examinedValueAssignment,
            new Statement(
                $this->assertionMethodInvocationFactory->create(
                    self::COMPARISON_TO_ASSERTION_TEMPLATE_MAP[$assertion->getComparison()],
                    [
                        $expectedValuePlaceholder,
                        $examinedValuePlaceholder,
                    ]
                )
            ),
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

    private function createAssertionStatement(
        AssertionInterface $assertion,
        VariablePlaceholder $valuePlaceholder
    ): StatementInterface {
        return new Statement(
            $this->assertionMethodInvocationFactory->create(
                'exists' === $assertion->getComparison() ? 'assertTrue' : 'assertFalse',
                [
                    $valuePlaceholder
                ],
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
