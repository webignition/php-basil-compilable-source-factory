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
        private Literal $exceptionClassCall,
        private Literal $exceptionCodeCall,
        private Literal $exceptionMessageCall,
    ) {
        parent::__construct([
            'statement' => new Statement($this->statement),
            'reason' => $this->reason,
            'exception' => [
                'class' => $this->exceptionClassCall,
                'code' => $this->exceptionCodeCall,
                'message' => $this->exceptionMessageCall,
            ],
        ]);
    }
}
