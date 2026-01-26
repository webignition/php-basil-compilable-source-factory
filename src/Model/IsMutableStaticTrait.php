<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

trait IsMutableStaticTrait
{
    private bool $isStatic = false;

    public function isStatic(): bool
    {
        return $this->isStatic;
    }

    public function setIsStatic(bool $isStatic): void
    {
        $this->isStatic = $isStatic;
    }
}
