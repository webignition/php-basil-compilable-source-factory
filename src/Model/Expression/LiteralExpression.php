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

    private function __construct(
        private readonly string $content,
        private readonly Type $type
    ) {}

    public function __toString(): string
    {
        return $this->content;
    }

    public static function string(string $content): LiteralExpression
    {
        return new LiteralExpression($content, Type::STRING);
    }

    public static function null(): LiteralExpression
    {
        return new LiteralExpression('null', Type::NULL);
    }

    public static function void(string $content): LiteralExpression
    {
        return new LiteralExpression($content, Type::VOID);
    }

    public static function boolean(bool $value): LiteralExpression
    {
        return new LiteralExpression($value ? 'true' : 'false', Type::BOOLEAN);
    }

    public static function integer(int $value): LiteralExpression
    {
        return new LiteralExpression((string) $value, Type::INTEGER);
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
