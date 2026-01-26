<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

trait IsNotStaticTrait
{
    public function isStatic(): false
    {
        return false;
    }
}
