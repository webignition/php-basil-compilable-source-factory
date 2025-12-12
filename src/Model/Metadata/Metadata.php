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
     * @param array<mixed> $components
     */
    public function __construct(array $components = [])
    {
        $classDependencies = $components[self::KEY_CLASS_DEPENDENCIES] ?? new ClassDependencyCollection();
        $classDependencies = $classDependencies instanceof ClassDependencyCollection
            ? $classDependencies
            : new ClassDependencyCollection();

        $emptyVariableDependencies = new VariableDependencyCollection();
        $variableDependencies = $components[self::KEY_VARIABLE_DEPENDENCIES] ?? $emptyVariableDependencies;
        $variableDependencies = $variableDependencies instanceof VariableDependencyCollection
            ? $variableDependencies
            : $emptyVariableDependencies;

        $this->classDependencies = $classDependencies;
        $this->variableDependencies = $variableDependencies;
    }

    /**
     * @param non-empty-string[] $classNames
     * @param VariableNames::*[] $variableNames
     */
    public static function create(array $classNames = [], array $variableNames = []): MetadataInterface
    {
        $classNameObjects = [];
        foreach ($classNames as $className) {
            $classNameObjects[] = new ClassName($className);
        }
        $classDependencies = new ClassDependencyCollection(new ClassNameCollection($classNameObjects));
        $variableDependencies = new VariableDependencyCollection($variableNames);

        $metadata = new Metadata();
        $metadata->classDependencies = $classDependencies;
        $metadata->variableDependencies = $variableDependencies;

        return $metadata;
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
