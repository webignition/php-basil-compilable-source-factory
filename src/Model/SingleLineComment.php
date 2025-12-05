<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

use webignition\BasilCompilableSourceFactory\Model\Body\BodyContentInterface;

class SingleLineComment implements \Stringable, BodyContentInterface
{
    use ResolvableStringableTrait;

    private string $content;

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    public function __toString(): string
    {
        return '// ' . $this->content;
    }
}
