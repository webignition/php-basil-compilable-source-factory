<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

trait NeverThrowsTrait
{
    public function mightThrow(): bool
    {
        return false;
    }
}
