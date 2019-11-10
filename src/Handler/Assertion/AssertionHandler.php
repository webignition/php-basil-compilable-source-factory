<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\Exception\UnknownObjectPropertyException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilationSource\Block\BlockInterface;
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
     * @return BlockInterface
     *
     * @throws UnsupportedModelException
     * @throws UnknownObjectPropertyException
     */
    public function handle(AssertionInterface $assertion): BlockInterface
    {
        if ($this->isComparisonAssertion($assertion)) {
            return $this->comparisonAssertionHandler->handle($assertion);
        }

        if ($this->isExistenceAssertion($assertion)) {
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
