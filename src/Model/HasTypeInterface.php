<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

interface HasTypeInterface
{
    public function getType(): TypeCollection;
}
