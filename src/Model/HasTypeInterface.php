<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

use webignition\BasilCompilableSourceFactory\Enum\Type;

interface HasTypeInterface
{
    /**
     * @return Type[]
     */
    public function getType(): array;
}
