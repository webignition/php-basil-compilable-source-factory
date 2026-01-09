<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Json;

use webignition\BasilCompilableSourceFactory\Model\Expression\JsonExpression;
use webignition\BasilModels\Model\StatementInterface;

readonly class FailureMessage extends JsonExpression
{
    /**
     * @param array<string, string> $context
     */
    public function __construct(
        StatementInterface $statement,
        string $reason,
        StringLiteral $exceptionClassCall,
        IntegerLiteral $exceptionCodeCall,
        StringLiteral $exceptionMessageCall,
        array $context,
    ) {
        $data = [
            'statement' => $statement->jsonSerialize(),
            'reason' => $reason,
            'exception' => [
                'class' => $exceptionClassCall,
                'code' => $exceptionCodeCall,
                'message' => $exceptionMessageCall,
            ],
        ];

        if ([] !== $context) {
            $data['context'] = $context;
        }

        parent::__construct($data);
    }
}
