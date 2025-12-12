<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Metadata;

use webignition\BasilCompilableSourceFactory\Model\Block\ClassDependencyCollection;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\ClassNameCollection;
use webignition\BasilCompilableSourceFactory\Model\VariableDependencyCollection;
use webignition\BasilCompilableSourceFactory\VariableNames;

class Metadata implements MetadataInterface
{
    public const KEY_CLASS_DEPENDENCIES = 'class-dependencies';
    public const KEY_VARIABLE_DEPENDENCIES = 'variable-dependencies';

    private ClassDependencyCollection $classDependencies;
    private VariableDependencyCollection $variableDependencies;

    /**
     * @param non-empty-string[] $classNames
     * @param VariableNames::*[] $variableNames
     */
    public function __construct(array $classNames = [], array $variableNames = [])
    {
        $classNameObjects = [];
        foreach ($classNames as $className) {
            $classNameObjects[] = new ClassName($className);
        }

        $this->classDependencies = new ClassDependencyCollection(new ClassNameCollection($classNameObjects));
        $this->variableDependencies = new VariableDependencyCollection($variableNames);
    }

    public function getClassDependencies(): ClassDependencyCollection
    {
        return $this->classDependencies;
    }

    public function getVariableDependencies(): VariableDependencyCollection
    {
        return $this->variableDependencies;
    }

    public function merge(MetadataInterface $metadata): MetadataInterface
    {
        $new = new Metadata();
        $new->classDependencies = $this->classDependencies->merge($metadata->getClassDependencies());
        $new->variableDependencies = $this->variableDependencies->merge($metadata->getVariableDependencies());

        return $new;
    }
}
