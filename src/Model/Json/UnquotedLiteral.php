<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Json;

readonly class UnquotedLiteral implements LiteralInterface
{
    public function __construct(
        public string $value,
    ) {}

    public function getValue(): string
    {
        return $this->value;
    }

    public function isQuotable(): bool
    {
        return false;
    }
}
