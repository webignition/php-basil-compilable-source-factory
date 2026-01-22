<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Statement;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStatementException;
use webignition\BasilModels\Model\Statement\StatementInterface;

class StatementHandler implements StatementHandlerInterface
{
    /**
     * @param StatementHandlerInterface[] $handlers
     */
    public function __construct(
        private array $handlers,
    ) {}

    public static function createHandler(): StatementHandler
    {
        return new StatementHandler([
            BrowserOperationActionHandler::createHandler(),
            InteractionActionHandler::createHandler(),
            SetActionHandler::createHandler(),
            WaitActionHandler::createHandler(),
            WaitForActionHandler::createHandler(),
            ComparisonAssertionHandler::createHandler(),
            ExistenceAssertionHandler::createHandler(),
            IsRegExpAssertionHandler::createHandler(),
        ]);
    }

    /**
     * @throws UnsupportedStatementException
     */
    public function handle(StatementInterface $statement): StatementHandlerComponents
    {
        $components = null;

        foreach ($this->handlers as $handler) {
            if (null === $components) {
                try {
                    $components = $handler->handle($statement);
                } catch (UnsupportedContentException $unsupportedContentException) {
                    throw new UnsupportedStatementException($statement, $unsupportedContentException);
                }
            }
        }

        if (null === $components) {
            throw new UnsupportedStatementException($statement);
        }

        return $components;
    }
}
