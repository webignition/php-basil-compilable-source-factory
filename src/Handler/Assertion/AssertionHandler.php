<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSource\Block\CodeBlock;
use webignition\BasilCompilableSource\Block\CodeBlockInterface;
use webignition\BasilCompilableSource\Line\ComparisonExpression;
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
    private $existenceAssertionHandler;
    private $comparisonAssertionHandler;
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

    public function __construct(
        ExistenceAssertionHandler $existenceComparisonHandler,
        ComparisonAssertionHandler $comparisonAssertionHandler,
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
        $this->existenceAssertionHandler = $existenceComparisonHandler;
        $this->comparisonAssertionHandler = $comparisonAssertionHandler;
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
            ExistenceAssertionHandler::createHandler(),
            ComparisonAssertionHandler::createHandler(),
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
                return $this->comparisonAssertionHandler->handle($assertion);
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
    public function handleExistenceAssertion(AssertionInterface $assertion): CodeBlockInterface
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
