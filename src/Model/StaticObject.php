<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;

class StaticObject implements \Stringable, ExpressionInterface
{
    use ResolvableStringableTrait;

    /**
     * @param non-empty-string $object
     */
    public function __construct(
        private readonly string $object
    ) {}

    public function __toString(): string
    {
        if (ClassName::isFullyQualifiedClassName($this->object)) {
            $className = new ClassName($this->object);

            return $className->renderClassName();
        }

        return $this->object;
    }

    public function getMetadata(): MetadataInterface
    {
        if (ClassName::isFullyQualifiedClassName($this->object)) {
            return new Metadata(classNames: [$this->object]);
        }

        return new Metadata();
    }
}
