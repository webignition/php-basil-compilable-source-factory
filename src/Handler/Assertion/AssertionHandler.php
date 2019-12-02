<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedIdentifierException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedAssertionException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedValueException;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilModels\Assertion\AssertionInterface;
use webignition\BasilModels\Assertion\ComparisonAssertionInterface;

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
     * @throws UnsupportedAssertionException
     */
    public function handle(AssertionInterface $assertion): CodeBlockInterface
    {
        try {
            if ($this->isComparisonAssertion($assertion) && $assertion instanceof ComparisonAssertionInterface) {
                return $this->comparisonAssertionHandler->handle($assertion);
            }

            if ($this->isExistenceAssertion($assertion)) {
                return $this->existenceComparisonHandler->handle($assertion);
            }
        } catch (
            UnsupportedIdentifierException |
            UnsupportedValueException $previous
        ) {
            throw new UnsupportedAssertionException($assertion, $previous);
        }

        throw new UnsupportedAssertionException($assertion);
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
