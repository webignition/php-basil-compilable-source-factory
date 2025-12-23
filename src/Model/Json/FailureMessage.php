<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Json;

use webignition\BasilCompilableSourceFactory\Model\Expression\JsonExpression;
use webignition\BasilModels\Model\StatementInterface;

readonly class FailureMessage extends JsonExpression
{
    public function __construct(
        StatementInterface $statement,
        string $reason,
        Literal $exceptionClassCall,
        Literal $exceptionCodeCall,
        Literal $exceptionMessageCall,
    ) {
        parent::__construct([
            'statement' => new Statement($statement),
            'reason' => $reason,
            'exception' => [
                'class' => $exceptionClassCall,
                'code' => $exceptionCodeCall,
                'message' => $exceptionMessageCall,
            ],
        ]);
    }
}
