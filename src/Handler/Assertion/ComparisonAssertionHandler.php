<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\AccessorDefaultValueFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\AssertionCallFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\VariableAssignmentFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedValueException;
use webignition\BasilCompilableSourceFactory\Handler\NamedDomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifierValue;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilDomIdentifierFactory\Factory as DomIdentifierFactory;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Assertion\ComparisonAssertionInterface;

class ComparisonAssertionHandler
{
    private const COMPARISON_TO_ASSERTION_TEMPLATE_MAP = [
        'includes' => AssertionCallFactory::ASSERT_STRING_CONTAINS_STRING_TEMPLATE,
        'excludes' => AssertionCallFactory::ASSERT_STRING_NOT_CONTAINS_STRING_TEMPLATE,
        'is' => AssertionCallFactory::ASSERT_EQUALS_TEMPLATE,
        'is-not' => AssertionCallFactory::ASSERT_NOT_EQUALS_TEMPLATE,
        'matches' => AssertionCallFactory::ASSERT_MATCHES_TEMPLATE,
    ];

    private $assertionCallFactory;
    private $scalarValueHandler;
    private $namedDomIdentifierHandler;
    private $identifierTypeAnalyser;
    private $variableAssignmentFactory;
    private $accessorDefaultValueFactory;
    private $domIdentifierFactory;

    public function __construct(
        AssertionCallFactory $assertionCallFactory,
        VariableAssignmentFactory $variableAssignmentFactory,
        ScalarValueHandler $scalarValueHandler,
        NamedDomIdentifierHandler $namedDomIdentifierHandler,
        AccessorDefaultValueFactory $accessorDefaultValueFactory,
        IdentifierTypeAnalyser $identifierTypeAnalyser,
        DomIdentifierFactory $domIdentifierFactory
    ) {
        $this->assertionCallFactory = $assertionCallFactory;
        $this->scalarValueHandler = $scalarValueHandler;
        $this->namedDomIdentifierHandler = $namedDomIdentifierHandler;
        $this->identifierTypeAnalyser = $identifierTypeAnalyser;
        $this->variableAssignmentFactory = $variableAssignmentFactory;
        $this->accessorDefaultValueFactory = $accessorDefaultValueFactory;
        $this->domIdentifierFactory = $domIdentifierFactory;
    }

    public static function createHandler(): ComparisonAssertionHandler
    {
        return new ComparisonAssertionHandler(
            AssertionCallFactory::createFactory(),
            VariableAssignmentFactory::createFactory(),
            ScalarValueHandler::createHandler(),
            NamedDomIdentifierHandler::createHandler(),
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
        $examinedValuePlaceholder = new VariablePlaceholder(VariableNames::EXAMINED_VALUE);
        $expectedValuePlaceholder = new VariablePlaceholder(VariableNames::EXPECTED_VALUE);

        $examinedValue = $assertion->getIdentifier();
        $expectedValue = $assertion->getValue();

        if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($examinedValue)) {
            $examinedValueDomIdentifier = $this->domIdentifierFactory->createFromIdentifierString($examinedValue);
            if (null === $examinedValueDomIdentifier) {
                throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $examinedValue);
            }

            $examinedValueAccessor = $this->namedDomIdentifierHandler->handle(
                new NamedDomIdentifierValue($examinedValueDomIdentifier, $examinedValuePlaceholder)
            );

            $examinedValueAccessor->mutateLastStatement(function (string $content) use ($examinedValuePlaceholder) {
                return str_replace((string) $examinedValuePlaceholder . ' = ', '', $content);
            });
        } else {
            $examinedValueAccessor = $this->scalarValueHandler->handle($examinedValue);
        }

        if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($expectedValue)) {
            $expectedValueDomIdentifier = $this->domIdentifierFactory->createFromIdentifierString($expectedValue);
            if (null === $expectedValueDomIdentifier) {
                throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $expectedValue);
            }

            $expectedValueAccessor = $this->namedDomIdentifierHandler->handle(
                new NamedDomIdentifierValue(
                    $expectedValueDomIdentifier,
                    $expectedValuePlaceholder
                )
            );

            $expectedValueAccessor->mutateLastStatement(function (string $content) use ($expectedValuePlaceholder) {
                return str_replace((string) $expectedValuePlaceholder . ' = ', '', $content);
            });
        } else {
            $expectedValueAccessor = $this->scalarValueHandler->handle($expectedValue);
        }

        $examinedValueAssignment = $this->variableAssignmentFactory->createForValueAccessor(
            $examinedValueAccessor,
            $examinedValuePlaceholder,
            $this->accessorDefaultValueFactory->createString($examinedValue)
        );

        $expectedValueAssignment = $this->variableAssignmentFactory->createForValueAccessor(
            $expectedValueAccessor,
            $expectedValuePlaceholder,
            $this->accessorDefaultValueFactory->createString($expectedValue)
        );

        return $this->assertionCallFactory->createValueComparisonAssertionCall(
            $expectedValueAssignment,
            $examinedValueAssignment,
            $expectedValuePlaceholder,
            $examinedValuePlaceholder,
            self::COMPARISON_TO_ASSERTION_TEMPLATE_MAP[$assertion->getComparison()]
        );
    }
}
