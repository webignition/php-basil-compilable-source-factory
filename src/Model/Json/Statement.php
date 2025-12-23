<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Json;

use webignition\BasilCompilableSourceFactory\Model\Expression\JsonExpression;
use webignition\BasilModels\Model\EncapsulatingStatementInterface;
use webignition\BasilModels\Model\StatementInterface;

readonly class Statement extends JsonExpression
{
    public function __construct(
        StatementInterface $statement,
    ) {
        $data = [
            'statement' => (string) $statement,
            'type' => $statement->getStatementType(),
        ];

        if ($statement instanceof EncapsulatingStatementInterface) {
            $data['source'] = new Statement($statement->getSourceStatement());
        }

        parent::__construct($data);
    }
}
