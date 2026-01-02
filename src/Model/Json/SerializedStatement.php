<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Json;

use webignition\BasilModels\Model\EncapsulatingStatementInterface;
use webignition\BasilModels\Model\StatementInterface;

readonly class SerializedStatement
{
    public function __construct(
        private StatementInterface $statement
    ) {}

    /**
     * @return array<mixed>
     */
    public function serialize(): array
    {
        if ($this->statement instanceof EncapsulatingStatementInterface) {
            $data = $this->statement->jsonSerialize();
        } else {
            $data = [
                'statement' => $this->statement->jsonSerialize(),
            ];
        }

        return $data;
    }
}
