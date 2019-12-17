<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\AccessorDefaultValueFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\AssertionCallFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\VariableAssignmentFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedIdentifierException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedValueException;
use webignition\BasilCompilableSourceFactory\Handler\DomIdentifierExistenceHandler;
use webignition\BasilCompilableSourceFactory\Handler\NamedDomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifierValue;
use webignition\BasilCompilableSourceFactory\ModelFactory\DomIdentifier\DomIdentifierFactory;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\VariablePlaceholder;
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
    private $domIdentifierFactory;
    private $identifierTypeAnalyser;
    private $variableAssignmentFactory;
    private $accessorDefaultValueFactory;
    private $domIdentifierExistenceHandler;

    public function __construct(
        AssertionCallFactory $assertionCallFactory,
        VariableAssignmentFactory $variableAssignmentFactory,
        ScalarValueHandler $scalarValueHandler,
        NamedDomIdentifierHandler $namedDomIdentifierHandler,
        AccessorDefaultValueFactory $accessorDefaultValueFactory,
        DomIdentifierFactory $domIdentifierFactory,
        IdentifierTypeAnalyser $identifierTypeAnalyser,
        DomIdentifierExistenceHandler $domIdentifierExistenceHandler
    ) {
        $this->assertionCallFactory = $assertionCallFactory;
        $this->scalarValueHandler = $scalarValueHandler;
        $this->namedDomIdentifierHandler = $namedDomIdentifierHandler;
        $this->domIdentifierFactory = $domIdentifierFactory;
        $this->identifierTypeAnalyser = $identifierTypeAnalyser;
        $this->variableAssignmentFactory = $variableAssignmentFactory;
        $this->accessorDefaultValueFactory = $accessorDefaultValueFactory;
        $this->domIdentifierExistenceHandler = $domIdentifierExistenceHandler;
    }

    public static function createHandler(): ComparisonAssertionHandler
    {
        return new ComparisonAssertionHandler(
            AssertionCallFactory::createFactory(),
            VariableAssignmentFactory::createFactory(),
            ScalarValueHandler::createHandler(),
            NamedDomIdentifierHandler::createHandler(),
            AccessorDefaultValueFactory::createFactory(),
            DomIdentifierFactory::createFactory(),
            new IdentifierTypeAnalyser(),
            DomIdentifierExistenceHandler::createHandler()
        );
    }

    /**
     * @param ComparisonAssertionInterface $assertion
     *
     * @return CodeBlockInterface
     *
     * @throws UnsupportedIdentifierException
     * @throws UnsupportedValueException
     */
    public function handle(ComparisonAssertionInterface $assertion): CodeBlockInterface
    {
        $examinedValuePlaceholder = new VariablePlaceholder(VariableNames::EXAMINED_VALUE);
        $expectedValuePlaceholder = new VariablePlaceholder(VariableNames::EXPECTED_VALUE);

        $examinedValue = $assertion->getIdentifier();
        $expectedValue = $assertion->getValue();

        if (
            $this->identifierTypeAnalyser->isDomIdentifier($examinedValue) ||
            $this->identifierTypeAnalyser->isDescendantDomIdentifier($examinedValue)
        ) {
            $examinedValueDomIdentifier = $this->domIdentifierFactory->create($examinedValue);

            $examinedValueExistence = $this->domIdentifierExistenceHandler->createExistenceAssertion(
                $examinedValueDomIdentifier,
                null === $examinedValueDomIdentifier->getAttributeName()
            );

            $examinedValueAccess = $this->namedDomIdentifierHandler->handle(
                new NamedDomIdentifierValue($examinedValueDomIdentifier, $examinedValuePlaceholder)
            );

            $examinedValueAccessor = new CodeBlock([
                $examinedValueExistence,
                $examinedValueAccess,
            ]);

            $examinedValueAccessor->mutateLastStatement(function (string $content) use ($examinedValuePlaceholder) {
                return str_replace((string) $examinedValuePlaceholder . ' = ', '', $content);
            });
        } else {
            $examinedValueAccessor = $this->scalarValueHandler->handle($examinedValue);
        }

        if (
            $this->identifierTypeAnalyser->isDomIdentifier($expectedValue) ||
            $this->identifierTypeAnalyser->isDescendantDomIdentifier($expectedValue)
        ) {
            $expectedValueDomIdentifier = $this->domIdentifierFactory->create($expectedValue);

            $expectedValueExistence = $this->domIdentifierExistenceHandler->createExistenceAssertion(
                $expectedValueDomIdentifier,
                null === $expectedValueDomIdentifier->getAttributeName()
            );

            $expectedValueAccess = $this->namedDomIdentifierHandler->handle(
                new NamedDomIdentifierValue(
                    $expectedValueDomIdentifier,
                    $expectedValuePlaceholder
                )
            );

            $expectedValueAccessor = new CodeBlock([
                $expectedValueExistence,
                $expectedValueAccess,
            ]);

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
