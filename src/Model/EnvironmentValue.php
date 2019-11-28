<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

class EnvironmentValue
{
    private const PREFIX = '$env.';

    private $property;
    private $default;

    public function __construct(string $property, ?string $default)
    {
        $this->property = $property;
        $this->default = $default;
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
