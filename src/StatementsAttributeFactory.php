<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSourceFactory\Model\Attribute\StatementsAttribute;
use webignition\BasilCompilableSourceFactory\Model\Expression\ArrayExpression\ArrayExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilModels\Model\Statement\StatementCollectionInterface;

readonly class StatementsAttributeFactory
{
    public function __construct(
        private StatementSerializer $statementSerializer,
    ) {}

    public static function createFactory(): self
    {
        return new StatementsAttributeFactory(
            StatementSerializer::createFactory(),
        );
    }

    public function create(StatementCollectionInterface $statements): StatementsAttribute
    {
        $serializedStatementExpressions = [];

        foreach ($statements as $statement) {
            $serializedStatement = $this->statementSerializer->serialize($statement);
            $serializedStatementExpressions[] = LiteralExpression::string("'" . $serializedStatement . "'");
        }

        $methodArguments = new MethodArguments([
            ArrayExpression::fromArray($serializedStatementExpressions, false),
        ]);

        return new StatementsAttribute($methodArguments);
    }
}
