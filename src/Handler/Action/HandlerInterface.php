<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\StatementHandlerComponents;
use webignition\BasilModels\Model\Action\ActionInterface;

interface HandlerInterface
{
    /**
     * @throws UnsupportedContentException
     */
    public function handle(ActionInterface $action): ?StatementHandlerComponents;
}
