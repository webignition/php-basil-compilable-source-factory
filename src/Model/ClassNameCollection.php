<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

class ClassNameCollection
{
    /**
     * @var ClassName[]
     */
    private array $classNames = [];

    /**
     * @param ClassName[] $classNames
     */
    public function __construct(array $classNames)
    {
        foreach ($classNames as $className) {
            if (false === $this->contains($className)) {
                $this->classNames[] = $className;
            }
        }
    }

    private function contains(ClassName $className): bool
    {
        $renderedClassName = (string) $className;

        foreach ($this->classNames as $className) {
            if ((string) $className === $renderedClassName) {
                return true;
            }
        }

        return false;
    }
}
