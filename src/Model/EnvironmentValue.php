<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

class EnvironmentValue
{
    private const PREFIX = '$env.';

    public function __construct(
        private string $property,
        private ?string $default = null
    ) {
    }

    public static function is(string $value): bool
    {
        return preg_match('/^' . preg_quote(self::PREFIX, '/') . '.+/', $value) > 0;
    }

    public function getProperty(): string
    {
        return $this->property;
    }

    public function getDefault(): ?string
    {
        return $this->default;
    }
}
