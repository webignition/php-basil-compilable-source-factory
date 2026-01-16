<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStatementException;
use webignition\BasilCompilableSourceFactory\Handler\StatementHandlerInterface;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\EmptyLine;
use webignition\BasilModels\Model\Assertion\AssertionInterface;

class AssertionHandler
{
    /**
     * @param StatementHandlerInterface[] $handlers
     */
    public function __construct(
        private array $handlers,
    ) {}

    public static function createHandler(): AssertionHandler
    {
        return new AssertionHandler([
            ComparisonAssertionHandler::createHandler(),
            ExistenceAssertionHandler::createHandler(),
            IsRegExpAssertionHandler::createHandler(),
        ]);
    }

    /**
     * @throws UnsupportedStatementException
     */
    public function handle(AssertionInterface $assertion): BodyInterface
    {
        $components = null;

        foreach ($this->handlers as $handler) {
            if (null === $components) {
                try {
                    $components = $handler->handle($assertion);
                } catch (UnsupportedContentException $unsupportedContentException) {
                    throw new UnsupportedStatementException($assertion, $unsupportedContentException);
                }
            }
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
