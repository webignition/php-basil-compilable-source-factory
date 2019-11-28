<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\Exception\UnknownObjectPropertyException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilModel\Assertion\AssertionComparison;
use webignition\BasilModel\Assertion\AssertionInterface;
use webignition\BasilModel\Assertion\ComparisonAssertionInterface;
use webignition\BasilModel\Assertion\ExaminationAssertionInterface;

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
     * @throws UnsupportedModelException
     * @throws UnknownObjectPropertyException
     */
    public function handle(AssertionInterface $assertion): CodeBlockInterface
    {
        if ($this->isComparisonAssertion($assertion) && $assertion instanceof ComparisonAssertionInterface) {
            return $this->comparisonAssertionHandler->handle($assertion);
        }

        if ($this->isExistenceAssertion($assertion) && $assertion instanceof ExaminationAssertionInterface) {
            return $this->existenceComparisonHandler->handle($assertion);
        }

        throw new UnsupportedModelException($assertion);
    }

    private function isComparisonAssertion(AssertionInterface $assertion): bool
    {
        if (!$assertion instanceof ComparisonAssertionInterface) {
            return false;
        }

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
        if (!$assertion instanceof ExaminationAssertionInterface) {
            return false;
        }

        return in_array($assertion->getComparison(), [AssertionComparison::EXISTS, AssertionComparison::NOT_EXISTS]);
    }
}
