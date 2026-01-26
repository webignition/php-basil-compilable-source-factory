<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Expression;

use webignition\BasilCompilableSourceFactory\Model\IsNotStaticTrait;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\NeverThrowsTrait;
use webignition\BasilCompilableSourceFactory\Model\ResolvableStringableTrait;

class LiteralExpression implements \Stringable, ExpressionInterface
{
    use ResolvableStringableTrait;
    use NeverThrowsTrait;
    use IsNotStaticTrait;

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
