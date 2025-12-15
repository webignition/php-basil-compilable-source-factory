<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Metadata;

use webignition\BasilModels\Model\Assertion\AssertionInterface;
use webignition\BasilModels\Model\StatementInterface;

readonly class Metadata implements \JsonSerializable
{
    public function __construct(
        private AssertionInterface $assertion,
        private ?StatementInterface $source = null,
    ) {}

    /**
     * @return array{
     *   assertion: string,
     *   source?: string
     * }
     */
    public function jsonSerialize(): array
    {
        $data = [
            'assertion' => (string) $this->assertion,
        ];

        if (null !== $this->source) {
            $data['source'] = (string) $this->source;
        }

        return $data;
    }
}
