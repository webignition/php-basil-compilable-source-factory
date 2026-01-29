<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Statement;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilModels\Model\Statement\StatementInterface;

interface StatementHandlerInterface
{
    /**
     * @throws UnsupportedContentException
     */
    public function handle(StatementInterface $statement): ?StatementHandlerCollections;
}
