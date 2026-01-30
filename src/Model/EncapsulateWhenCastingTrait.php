<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

trait EncapsulateWhenCastingTrait
{
    public function encapsulateWhenCasting(): bool
    {
        return true;
    }
}
