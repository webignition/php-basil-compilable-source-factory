<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

use webignition\BasilCompilableSourceFactory\Enum\DependencyName;

/**
 * @implements \IteratorAggregate<string, Property>
 */
class VariableDependencyCollection implements \IteratorAggregate
{
    /**
     * @var array<string, Property>
     */
    private array $dependencies = [];

    /**
     * @param DependencyName[] $names
     */
    public function __construct(array $names = [])
    {
        foreach ($names as $name) {
            $this->add(Property::asDependency($name));
        }
    }

    public function merge(VariableDependencyCollection $collection): VariableDependencyCollection
    {
        $new = clone $this;

        foreach ($collection as $dependency) {
            $new->add($dependency);
        }

        return $new;
    }

    /**
     * @return \Traversable<string, Property>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->dependencies);
    }

    private function add(Property $dependency): void
    {
        if (false === $dependency->getIsDependency()) {
            return;
        }

        $name = $dependency->getName();
        if (array_key_exists($name, $this->dependencies)) {
            return;
        }

        $this->dependencies[$name] = $dependency;
    }
}
