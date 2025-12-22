<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Metadata;

use webignition\BasilCompilableSourceFactory\Renderable\Statement;
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
     *   statement: Statement,
     *   context?: array<string, string>,
     * }
     */
    public function jsonSerialize(): array
    {
        $data = [
            'statement' => new Statement($this->statement),
        ];

        if ([] !== $this->context) {
            $data['context'] = $this->context;
        }

        return $data;
    }
}
