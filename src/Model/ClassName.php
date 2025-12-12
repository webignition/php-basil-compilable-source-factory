<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

class ClassName implements \Stringable
{
    private const FQCN_PART_DELIMITER = '\\';

    /**
     * @var string[]
     */
    private static array $specialClasses = [
        'self',
        'static',
        'parent',
    ];

    /**
     * @param non-empty-string      $className
     * @param null|non-empty-string $alias
     */
    public function __construct(
        private readonly string $className,
        private readonly ?string $alias = null
    ) {}

    public function __toString(): string
    {
        return $this->alias ?? $this->getClass();
    }

    public static function isFullyQualifiedClassName(string $className): bool
    {
        if (in_array($className, self::$specialClasses)) {
            return false;
        }

        return strtolower($className) !== $className;
    }

    public function getClass(): string
    {
        $classNameParts = explode(self::FQCN_PART_DELIMITER, $this->className);

        return array_pop($classNameParts);
    }

    /**
     * @return non-empty-string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @return null|non-empty-string
     */
    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function isInRootNamespace(): bool
    {
        return $this->getClass() === $this->className;
    }

    public function renderClassName(): string
    {
        if (is_string($this->alias)) {
            return $this->alias;
        }

        $rendered = $this->getClass();
        if ($this->isInRootNamespace()) {
            $rendered = '\\' . $rendered;
        }

        return $rendered;
    }
}
