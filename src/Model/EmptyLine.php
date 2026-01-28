<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

use webignition\BasilCompilableSourceFactory\Model\Body\BodyContentInterface;

class EmptyLine implements \Stringable, BodyContentInterface
{
    use ResolvableStringableTrait;
    use NeverThrowsTrait;

    public function __toString(): string
    {
        return '';
    }
}
