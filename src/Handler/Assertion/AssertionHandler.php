<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStatementException;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\EmptyLine;
use webignition\BasilModels\Model\Assertion\AssertionInterface;

class AssertionHandler
{
    public function __construct(
        private ComparisonAssertionHandler $comparisonAssertionHandler,
        private ExistenceAssertionHandler $existenceAssertionHandler,
        private IsRegExpAssertionHandler $isRegExpAssertionHandler
    ) {}

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
        $components = null;

        try {
            if ($assertion->isComparison()) {
                $components = $this->comparisonAssertionHandler->handle($assertion);
            }

            if (in_array($assertion->getOperator(), ['exists', 'not-exists'])) {
                $components = $this->existenceAssertionHandler->handle($assertion);
            }

            if ('is-regexp' === $assertion->getOperator()) {
                $components = $this->isRegExpAssertionHandler->handle($assertion);
            }
        } catch (UnsupportedContentException $previous) {
            throw new UnsupportedStatementException($assertion, $previous);
        }

        if (null === $components) {
            throw new UnsupportedStatementException($assertion);
        }

        $bodyComponents = [];
        $setup = $components->getSetup();
        if ($setup instanceof BodyInterface) {
            $bodyComponents[] = $setup;
            $bodyComponents[] = new EmptyLine();
        }

        $bodyComponents[] = $components->getBody();

        return new Body($bodyComponents);
    }
}
