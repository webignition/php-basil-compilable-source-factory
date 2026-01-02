<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilModels\Enum\StatementType;
use webignition\BasilModels\Model\StatementInterface;

readonly class IndexedStatement implements StatementInterface
{
    public function __construct(
        private StatementInterface $statement,
        private int $index,
    ) {}

    public function __toString(): string
    {
        return $this->statement->__toString();
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        return array_merge(
            $this->statement->jsonSerialize(),
            [
                'index' => $this->index,
            ]
        );
    }

    public function getIdentifier(): ?string
    {
        return $this->statement->getIdentifier();
    }

    public function getSource(): string
    {
        return $this->statement->getSource();
    }

    public function getStatementType(): StatementType
    {
        return $this->statement->getStatementType();
    }

    public function getValue(): ?string
    {
        return $this->statement->getValue();
    }
}
