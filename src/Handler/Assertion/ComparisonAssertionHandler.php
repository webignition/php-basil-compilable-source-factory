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
use webignition\BasilCompilableSource\VariablePlaceholder;
use webignition\BasilCompilableSourceFactory\AccessorDefaultValueFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\AssertionCallFactory;
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
    private const COMPARISON_TO_ASSERTION_TEMPLATE_MAP = [
        'includes' => AssertionCallFactory::ASSERT_STRING_CONTAINS_STRING_METHOD,
        'excludes' => AssertionCallFactory::ASSERT_STRING_NOT_CONTAINS_STRING_METHOD,
        'is' => AssertionCallFactory::ASSERT_EQUALS_METHOD,
        'is-not' => AssertionCallFactory::ASSERT_NOT_EQUALS_METHOD,
        'matches' => AssertionCallFactory::ASSERT_MATCHES_METHOD,
    ];

    private $assertionCallFactory;
    private $scalarValueHandler;
    private $domIdentifierHandler;
    private $identifierTypeAnalyser;
    private $accessorDefaultValueFactory;
    private $domIdentifierFactory;

    public function __construct(
        AssertionCallFactory $assertionCallFactory,
        ScalarValueHandler $scalarValueHandler,
        DomIdentifierHandler $domIdentifierHandler,
        AccessorDefaultValueFactory $accessorDefaultValueFactory,
        IdentifierTypeAnalyser $identifierTypeAnalyser,
        DomIdentifierFactory $domIdentifierFactory
    ) {
        $this->assertionCallFactory = $assertionCallFactory;
        $this->scalarValueHandler = $scalarValueHandler;
        $this->domIdentifierHandler = $domIdentifierHandler;
        $this->identifierTypeAnalyser = $identifierTypeAnalyser;
        $this->accessorDefaultValueFactory = $accessorDefaultValueFactory;
        $this->domIdentifierFactory = $domIdentifierFactory;
    }

    public static function createHandler(): ComparisonAssertionHandler
    {
        return new ComparisonAssertionHandler(
            AssertionCallFactory::createFactory(),
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

        return new CodeBlock([
            $expectedValueAssignment,
            $examinedValueAssignment,
            new Statement(
                $this->assertionCallFactory->createValueComparisonAssertionCall(
                    $expectedValuePlaceholder,
                    $examinedValuePlaceholder,
                    self::COMPARISON_TO_ASSERTION_TEMPLATE_MAP[$assertion->getComparison()]
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
