<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilModels\Model\Statement\StatementInterface;

class StatementSerializer
{
    public static function createFactory(): self
    {
        return new StatementSerializer();
    }

    public function serialize(StatementInterface $statement): string
    {
        $serializedStatement = (string) json_encode($statement, JSON_PRETTY_PRINT);

        return addcslashes($serializedStatement, "'");
    }
}
