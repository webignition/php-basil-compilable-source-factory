<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Body;

use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\HasMetadataInterface as HasMetadata;
use webignition\BasilCompilableSourceFactory\Model\HasReturnTypeInterface as HasReturnType;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MightThrowInterface as MightThrow;
use webignition\BasilCompilableSourceFactory\Model\ReturnableInterface;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\TypeCollection;

/**
 * @implements \IteratorAggregate<BodyContentInterface>
 */
class BodyContentCollection implements \IteratorAggregate, MightThrow, HasReturnType, HasMetadata
{
    /**
     * @var BodyContentInterface[]
     */
    private array $items = [];

    public function append(BodyContentInterface $item): self
    {
        $new = clone $this;
        $new->items[] = $item;

        return $new;
    }

    public function merge(BodyContentCollection $collection): self
    {
        $new = clone $this;
        $new->items = array_merge($new->items, $collection->items);

        return $new;
    }

    /**
     * @param ExpressionInterface[] $expressions
     */
    public static function createFromExpressions(array $expressions): self
    {
        $statements = [];
        foreach ($expressions as $expression) {
            $statements[] = new Statement($expression);
        }

        $collection = new BodyContentCollection();

        foreach ($statements as $statement) {
            $collection = $collection->append($statement);
        }

        return $collection;
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->items);
    }

    public function getReturnType(): ?TypeCollection
    {
        $type = null;

        foreach ($this->items as $item) {
            if ($item instanceof ReturnableInterface) {
                $returnType = $item->getReturnType();
                if (null === $returnType) {
                    continue;
                }

                $type = null === $type ? $returnType : $type->merge($returnType);
            }
        }

        return $type;
    }

    public function getMetadata(): MetadataInterface
    {
        $metadata = new Metadata();

        foreach ($this->items as $item) {
            if ($item instanceof HasMetadata) {
                $metadata = $metadata->merge($item->getMetadata());
            }
        }

        return $metadata;
    }

    public function mightThrow(): bool
    {
        foreach ($this->items as $item) {
            if ($item->mightThrow()) {
                return true;
            }
        }

        return false;
    }
}
