<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\Exception\UnknownIdentifierException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedAssertionException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedValueException;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilDataStructure\AssertionInterface;
use webignition\BasilModel\Assertion\AssertionComparison;

class AssertionHandler
{
    private $existenceComparisonHandler;
    private $comparisonAssertionHandler;

    public function __construct(
        ExistenceComparisonHandler $existenceComparisonHandler,
        ComparisonAssertionHandler $comparisonAssertionHandler
    ) {
        $this->existenceComparisonHandler = $existenceComparisonHandler;
        $this->comparisonAssertionHandler = $comparisonAssertionHandler;
    }

    public static function createHandler(): AssertionHandler
    {
        return new AssertionHandler(
            ExistenceComparisonHandler::createHandler(),
            ComparisonAssertionHandler::createHandler()
        );
    }

    /**
     * @param AssertionInterface $assertion
     *
     * @return CodeBlockInterface
     *
     * @throws UnknownIdentifierException
     * @throws UnsupportedAssertionException
     * @throws UnsupportedValueException
     */
    public function handle(AssertionInterface $assertion): CodeBlockInterface
    {
        if ($this->isComparisonAssertion($assertion)) {
            return $this->comparisonAssertionHandler->handle($assertion);
        }

        if ($this->isExistenceAssertion($assertion)) {
            return $this->existenceComparisonHandler->handle($assertion);
        }

        throw new UnsupportedAssertionException($assertion);
    }

    private function isComparisonAssertion(AssertionInterface $assertion): bool
    {
        return in_array($assertion->getComparison(), [
            AssertionComparison::INCLUDES,
            AssertionComparison::EXCLUDES,
            AssertionComparison::IS,
            AssertionComparison::IS_NOT,
            AssertionComparison::MATCHES,
        ]);
    }

    private function isExistenceAssertion(AssertionInterface $assertion): bool
    {
        return in_array($assertion->getComparison(), [AssertionComparison::EXISTS, AssertionComparison::NOT_EXISTS]);
    }
}
