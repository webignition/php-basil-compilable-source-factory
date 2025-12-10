<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Metadata;

use webignition\BasilModels\Model\Assertion\AssertionInterface;

readonly class Metadata implements \JsonSerializable
{
    /**
     * @param non-empty-string $stepName
     */
    public function __construct(
        private string $stepName,
        private AssertionInterface $assertion,
    ) {}

    /**
     * @return array{
     *   step: non-empty-string,
     *   statement: string
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'step' => $this->stepName,
            'statement' => (string) $this->assertion,
        ];
    }
}
