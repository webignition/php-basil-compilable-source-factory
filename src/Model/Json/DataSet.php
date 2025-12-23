<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Json;

readonly class DataSet
{
    /**
     * @param array<mixed> $placeholders
     * @param array<mixed> $data
     */
    public function __construct(
        public array $placeholders,
        public array $data,
    ) {}
}
