<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Metadata;

use webignition\BasilModels\Model\EncapsulatingStatementInterface;
use webignition\BasilModels\Model\StatementInterface;

readonly class Metadata implements \JsonSerializable
{
    public function __construct(
        private StatementInterface $statement,
    ) {}

    /**
     * @return array{
     *   statement: string,
     *   source?: string
     * }
     */
    public function jsonSerialize(): array
    {
        $data = [
            'statement' => (string) $this->statement,
        ];

        if ($this->statement instanceof EncapsulatingStatementInterface) {
            $data['source'] = (string) $this->statement->getSourceStatement();
        }

        return $data;
    }
}
