<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Renderable;

use webignition\BasilModels\Model\EncapsulatingStatementInterface;
use webignition\BasilModels\Model\StatementInterface;

readonly class Statement implements \JsonSerializable
{
    public function __construct(
        private StatementInterface $statement,
    ) {}

    /**
     * @return array{
     *   statement: string,
     *   type: string,
     *   source?: Statement
     * }
     */
    public function jsonSerialize(): array
    {
        $data = [
            'statement' => (string) $this->statement,
            'type' => $this->statement->getStatementType(),
        ];

        if ($this->statement instanceof EncapsulatingStatementInterface) {
            $data['source'] = new Statement($this->statement->getSourceStatement());
        }

        return $data;
    }
}
