<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

interface HasReturnTypeInterface
{
    public function getReturnType(): ?TypeCollection;
}
