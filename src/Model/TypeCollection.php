<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

use webignition\BasilCompilableSourceFactory\Enum\Type;
use webignition\Stubble\Resolvable\ResolvableInterface;

class TypeCollection implements ResolvableInterface, \Stringable
{
    use ResolvableStringableTrait;

    /**
     * @param non-empty-array<Type> $types
     */
    public function __construct(
        private readonly array $types,
    ) {}

    public function __toString(): string
    {
        $values = [];
        foreach ($this->types as $type) {
            $values[] = $type->value;
        }

        sort($values);

        $content = '';

        foreach ($values as $value) {
            $content .= $value . '|';
        }

        return rtrim($content, '|');
    }

    public static function object(): TypeCollection
    {
        return new TypeCollection([Type::OBJECT]);
    }

    public static function boolean(): TypeCollection
    {
        return new TypeCollection([Type::BOOLEAN]);
    }

    public static function void(): TypeCollection
    {
        return new TypeCollection([Type::VOID]);
    }

    public static function string(): TypeCollection
    {
        return new TypeCollection([Type::STRING]);
    }

    public static function integer(): TypeCollection
    {
        return new TypeCollection([Type::INTEGER]);
    }

    public static function null(): TypeCollection
    {
        return new TypeCollection([Type::NULL]);
    }

    public static function array(): TypeCollection
    {
        return new TypeCollection([Type::ARRAY]);
    }

    public function merge(TypeCollection $collection): TypeCollection
    {
        $types = $this->types;
        foreach ($collection->types as $type) {
            if (!in_array($type, $types)) {
                $types[] = $type;
            }
        }

        return new TypeCollection($types);
    }
}
