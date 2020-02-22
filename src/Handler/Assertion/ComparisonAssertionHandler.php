<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSource\Block\CodeBlock;
use webignition\BasilCompilableSource\Block\CodeBlockInterface;
use webignition\BasilCompilableSource\Line\ClosureExpression;
use webignition\BasilCompilableSource\Line\ComparisonExpression;
use webignition\BasilCompilableSource\Line\ExpressionInterface;
use webignition\BasilCompilableSource\Line\LiteralExpression;
use webignition\BasilCompilableSource\Line\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\Line\Statement\AssignmentStatement;
use webignition\BasilCompilableSource\Line\Statement\Statement;
use webignition\BasilCompilableSource\VariablePlaceholder;
use webignition\BasilCompilableSourceFactory\AccessorDefaultValueFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\DomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilableSourceFactory\Model\DomIdentifierValue;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilDomIdentifierFactory\Factory as DomIdentifierFactory;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Assertion\ComparisonAssertionInterface;

class ComparisonAssertionHandler
{
    public const ASSERT_TRUE_METHOD = 'assertTrue';
    public const ASSERT_FALSE_METHOD = 'assertFalse';
    public const ASSERT_EQUALS_METHOD = 'assertEquals';
    public const ASSERT_NOT_EQUALS_METHOD = 'assertNotEquals';
    public const ASSERT_STRING_CONTAINS_STRING_METHOD = 'assertStringContainsString';
    public const ASSERT_STRING_NOT_CONTAINS_STRING_METHOD = 'assertStringNotContainsString';
    public const ASSERT_MATCHES_METHOD = 'assertRegExp';

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

    private $scalarValueHandler;
    private $domIdentifierHandler;
    private $identifierTypeAnalyser;
    private $accessorDefaultValueFactory;
    private $domIdentifierFactory;

    public function __construct(
        ScalarValueHandler $scalarValueHandler,
        DomIdentifierHandler $domIdentifierHandler,
        AccessorDefaultValueFactory $accessorDefaultValueFactory,
        IdentifierTypeAnalyser $identifierTypeAnalyser,
        DomIdentifierFactory $domIdentifierFactory
    ) {
        $this->scalarValueHandler = $scalarValueHandler;
        $this->domIdentifierHandler = $domIdentifierHandler;
        $this->identifierTypeAnalyser = $identifierTypeAnalyser;
        $this->accessorDefaultValueFactory = $accessorDefaultValueFactory;
        $this->domIdentifierFactory = $domIdentifierFactory;
    }

    public static function createHandler(): ComparisonAssertionHandler
    {
        return new ComparisonAssertionHandler(
            ScalarValueHandler::createHandler(),
            DomIdentifierHandler::createHandler(),
            AccessorDefaultValueFactory::createFactory(),
            IdentifierTypeAnalyser::create(),
            DomIdentifierFactory::createFactory()
        );
    }

    /**
     * @param ComparisonAssertionInterface $assertion
     *
     * @return CodeBlockInterface
     *
     * @throws UnsupportedContentException
     */
    public function handle(ComparisonAssertionInterface $assertion): CodeBlockInterface
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
                new ObjectMethodInvocation(
                    VariablePlaceholder::createDependency(VariableNames::PHPUNIT_TEST_CASE),
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
}
