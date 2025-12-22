<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Metadata;

use webignition\BasilModels\Model\EncapsulatingStatementInterface;
use webignition\BasilModels\Model\StatementInterface;

readonly class Metadata implements \JsonSerializable
{
    /**
     * @param array<string, string> $context
     */
    public function __construct(
        private StatementInterface $statement,
        private array $context = [],
    ) {}

    /**
     * @return array{
     *   statement: string,
     *   source?: string,
     *   context?: array<string, string>,
     * }
     */
    public function jsonSerialize(): array
    {
        $data = [
            'statement' => (string) $this->statement,
            'type' => $this->statement->getStatementType(),
        ];

        if ($this->statement instanceof EncapsulatingStatementInterface) {
            $data['source'] = (string) $this->statement->getSourceStatement();
        }

        if ([] !== $this->context) {
            $data['context'] = $this->context;
        }

        return $data;
    }
}
