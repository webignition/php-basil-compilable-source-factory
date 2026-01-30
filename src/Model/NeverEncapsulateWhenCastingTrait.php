<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

trait NeverEncapsulateWhenCastingTrait
{
    public function encapsulateWhenCasting(): bool
    {
        return false;
    }
}
