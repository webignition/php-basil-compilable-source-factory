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
        StringLiteral $exceptionClassCall,
        IntegerLiteral $exceptionCodeCall,
        StringLiteral $exceptionMessageCall,
    ) {
        parent::__construct(array_merge(
            [
                'statement' => $statement->jsonSerialize(),
                'reason' => $reason,
                'exception' => [
                    'class' => $exceptionClassCall,
                    'code' => $exceptionCodeCall,
                    'message' => $exceptionMessageCall,
                ],
            ]
        ));
    }
}
