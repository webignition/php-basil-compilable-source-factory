<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Json;

use webignition\BasilCompilableSourceFactory\Model\Expression\JsonExpression;
use webignition\BasilModels\Model\StatementInterface;

readonly class FailureMessage extends JsonExpression
{
    public function __construct(
        private StatementInterface $statement,
        private string $reason,
    ) {
        parent::__construct([
            'statement' => new Statement($this->statement),
            'reason' => $this->reason,
        ]);
    }
}
