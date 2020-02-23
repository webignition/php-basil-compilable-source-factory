<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSource\Block\CodeBlockInterface;
use webignition\BasilCompilableSourceFactory\AccessorDefaultValueFactory;
use webignition\BasilCompilableSourceFactory\AssertionFailureMessageFactory;
use webignition\BasilCompilableSourceFactory\AssertionMethodInvocationFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStatementException;
use webignition\BasilCompilableSourceFactory\Handler\DomIdentifierExistenceHandler;
use webignition\BasilCompilableSourceFactory\Handler\DomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilableSourceFactory\ValueTypeIdentifier;
use webignition\BasilDomIdentifierFactory\Factory as DomIdentifierFactory;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Assertion\AssertionInterface;
use webignition\BasilModels\Assertion\ComparisonAssertionInterface;

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
                return $this->existenceAssertionHandler->handle($assertion);
            }
        } catch (UnsupportedContentException $previous) {
            throw new UnsupportedStatementException($assertion, $previous);
        }

        throw new UnsupportedStatementException($assertion);
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
