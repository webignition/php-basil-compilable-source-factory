<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Metadata;

readonly class Metadata implements \JsonSerializable
{
    /**
     * @param non-empty-string $stepName
     * @param non-empty-string $statement
     */
    public function __construct(
        private string $stepName,
        private string $statement,
    ) {}

    /**
     * @return array{
     *   step: non-empty-string,
     *   statement: non-empty-string
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'step' => $this->stepName,
            'statement' => $this->statement,
        ];
    }
}
