<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Renderable;

use webignition\BasilModels\Model\StatementInterface;

readonly class FailureMessage implements \JsonSerializable
{
    public function __construct(
        private StatementInterface $statement,
        private string $reason,
    ) {}

    /**
     * @return array{
     *   statement: Statement,
     *   reason: string,
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'statement' => new Statement($this->statement),
            'reason' => $this->reason,
        ];
    }
}
