<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Metadata;

use webignition\BasilCompilableSourceFactory\Model\Block\ClassDependencyCollection;
use webignition\BasilCompilableSourceFactory\Model\VariableDependencyCollection;

interface MetadataInterface
{
    public function getClassDependencies(): ClassDependencyCollection;

    public function getVariableDependencies(): VariableDependencyCollection;

    public function merge(MetadataInterface $metadata): MetadataInterface;
}
