<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

/**
 * @implements \IteratorAggregate<string, ClassName>
 */
class ClassNameCollection implements \IteratorAggregate, \Countable
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

    public function merge(ClassNameCollection $collection): ClassNameCollection
    {
        return new ClassNameCollection(array_merge($this->classNames, $collection->classNames));
    }

    public function count(): int
    {
        return count($this->classNames);
    }

    /**
     * @return \Traversable<string, ClassName>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->classNames);
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
