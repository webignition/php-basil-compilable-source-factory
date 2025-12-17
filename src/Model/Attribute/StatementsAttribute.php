<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Attribute;

use webignition\BaseBasilTestCase\Attribute\Statements;
use webignition\BasilCompilableSourceFactory\Model\ClassName;

class StatementsAttribute extends Attribute implements AttributeInterface
{
    /**
     * @param non-empty-string $serializedStatements
     */
    public function __construct(string $serializedStatements)
    {
        parent::__construct(new ClassName(Statements::class), [$serializedStatements]);
    }
}
