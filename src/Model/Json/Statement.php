<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Json;

use webignition\BasilCompilableSourceFactory\Model\Expression\JsonExpression;
use webignition\BasilModels\Model\StatementInterface;

readonly class Statement extends JsonExpression
{
    public function __construct(StatementInterface $statement)
    {
        parent::__construct($statement->jsonSerialize());
    }
}
