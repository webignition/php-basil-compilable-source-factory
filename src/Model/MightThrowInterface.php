<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

interface MightThrowInterface
{
    public function mightThrow(): bool;
}
