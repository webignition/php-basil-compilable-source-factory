<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Json;

readonly class Literal
{
    public function __construct(
        public string $value,
    ) {}
}
