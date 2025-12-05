<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Block;

use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\ClassNameCollection;

class RenderableClassDependencyCollection extends ClassDependencyCollection
{
    public function __construct(ClassNameCollection $classNames)
    {
        parent::__construct($classNames->filter(function (ClassName $className) {
            if (false === $className->isInRootNamespace()) {
                return true;
            }

            return is_string($className->getAlias());
        }));
    }
}
