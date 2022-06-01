<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Expression;

use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\ResolvableStringableTrait;

class LiteralExpression implements ExpressionInterface
{
    use ResolvableStringableTrait;

    private string $content;

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    public function __toString(): string
    {
        return $this->content;
    }

    public function getMetadata(): MetadataInterface
    {
        return new Metadata();
    }
}
