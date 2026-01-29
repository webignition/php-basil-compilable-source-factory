<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

use webignition\BasilCompilableSourceFactory\Enum\Type;
use webignition\Stubble\Resolvable\ResolvableInterface;

readonly class TypeCollection implements ResolvableInterface
{
    /**
     * @param non-empty-array<Type> $types
     */
    public function __construct(
        private array $types,
    ) {}

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

    public function getTemplate(): string
    {
        $template = '';

        foreach ($this->types as $index => $type) {
            $template .= '{{ type_' . $index . ' }}|';
        }

        return rtrim($template, '|');
    }

    public function getContext(): array
    {
        $values = [];
        foreach ($this->types as $type) {
            $values[] = $type->value;
        }

        sort($values);

        $context = [];
        foreach ($values as $index => $value) {
            $context['type_' . $index] = $value;
        }

        return $context;
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
