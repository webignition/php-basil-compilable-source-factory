<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSource\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStatementException;
use webignition\BasilModels\Assertion\AssertionInterface;

class AssertionHandler
{
    public function __construct(
        private ComparisonAssertionHandler $comparisonAssertionHandler,
        private ExistenceAssertionHandler $existenceAssertionHandler,
        private IsRegExpAssertionHandler $isRegExpAssertionHandler
    ) {
    }

    public static function createHandler(): AssertionHandler
    {
        return new AssertionHandler(
            ComparisonAssertionHandler::createHandler(),
            ExistenceAssertionHandler::createHandler(),
            IsRegExpAssertionHandler::createHandler()
        );
    }

    /**
     * @throws UnsupportedStatementException
     */
    public function handle(AssertionInterface $assertion): BodyInterface
    {
        try {
            if ($assertion->isComparison()) {
                return $this->comparisonAssertionHandler->handle($assertion);
            }

            if (in_array($assertion->getOperator(), ['exists', 'not-exists'])) {
                return $this->existenceAssertionHandler->handle($assertion);
            }

            if ('is-regexp' === $assertion->getOperator()) {
                return $this->isRegExpAssertionHandler->handle($assertion);
            }
        } catch (UnsupportedContentException $previous) {
            throw new UnsupportedStatementException($assertion, $previous);
        }

        throw new UnsupportedStatementException($assertion);
    }
}
