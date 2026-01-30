<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

interface ReturnableInterface
{
    public function getReturnType(): ?TypeCollection;
}
