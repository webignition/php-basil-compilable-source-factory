<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

use webignition\BasilCompilableSourceFactory\Model\Block\ClassDependencyCollection;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;

class StaticObject implements ExpressionInterface
{
    use ResolvableStringableTrait;

    private string $object;

    public function __construct(string $object)
    {
        $this->object = $object;
    }

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
            return new Metadata([
                Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection(
                    new ClassNameCollection([new ClassName($this->object)])
                ),
            ]);
        }

        return new Metadata();
    }
}
