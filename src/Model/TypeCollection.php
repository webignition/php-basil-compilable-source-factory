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
}
