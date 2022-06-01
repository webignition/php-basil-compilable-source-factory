<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

use webignition\BasilCompilableSourceFactory\Model\Body\BodyContentInterface;

class EmptyLine implements BodyContentInterface
{
    use ResolvableStringableTrait;

    public function __toString(): string
    {
        return '';
    }
}
