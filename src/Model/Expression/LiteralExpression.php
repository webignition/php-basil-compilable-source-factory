<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Expression;

use webignition\BasilCompilableSourceFactory\Model\IsNotStaticTrait;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\NeverEncapsulateWhenCastingTrait;
use webignition\BasilCompilableSourceFactory\Model\NeverThrowsTrait;
use webignition\BasilCompilableSourceFactory\Model\ResolvableStringableTrait;
use webignition\BasilCompilableSourceFactory\Model\TypeCollection;

class LiteralExpression implements \Stringable, ExpressionInterface
{
    use ResolvableStringableTrait;
    use NeverThrowsTrait;
    use IsNotStaticTrait;
    use NeverEncapsulateWhenCastingTrait;

    private function __construct(
        private readonly string $content,
        private readonly TypeCollection $type
    ) {}

    public function __toString(): string
    {
        return $this->content;
    }

    public static function string(string $content): LiteralExpression
    {
        return new LiteralExpression($content, TypeCollection::string());
    }

    public static function null(): LiteralExpression
    {
        return new LiteralExpression('null', TypeCollection::null());
    }

    public static function void(string $content): LiteralExpression
    {
        return new LiteralExpression($content, TypeCollection::void());
    }

    public static function boolean(bool $value): LiteralExpression
    {
        return new LiteralExpression($value ? 'true' : 'false', TypeCollection::boolean());
    }

    public static function integer(int $value): LiteralExpression
    {
        return new LiteralExpression((string) $value, TypeCollection::integer());
    }

    public function getMetadata(): MetadataInterface
    {
        return new Metadata();
    }

    public function getType(): TypeCollection
    {
        return $this->type;
    }
}
