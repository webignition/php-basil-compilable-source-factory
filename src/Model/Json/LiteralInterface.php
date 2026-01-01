<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Json;

interface LiteralInterface
{
    public function getValue(): string;

    public function isQuotable(): bool;
}
