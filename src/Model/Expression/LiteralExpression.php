<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Expression;

use webignition\BasilCompilableSourceFactory\Enum\Type;
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

    public function __construct(
        private readonly string $content,
        private readonly Type $type
    ) {}

    public function __toString(): string
    {
        return $this->content;
    }

    public function getMetadata(): MetadataInterface
    {
        return new Metadata();
    }

    public function getType(): array
    {
        return [$this->type];
    }
}
