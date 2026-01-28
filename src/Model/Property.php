<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Enum\Type;
use webignition\BasilCompilableSourceFactory\Model\Expression\ClassObject;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;

class Property implements ExpressionInterface, IsAssigneeInterface
{
    use IsMutableStaticTrait;

    /**
     * @param non-empty-string $name
     */
    public function __construct(
        private readonly string $name,
        private readonly Type $type,
        private readonly ?ExpressionInterface $parent = null,
    ) {}

    /**
     * @param non-empty-string $name
     */
    public static function asVariable(string $name, Type $type): self
    {
        return new Property(name: $name, type: $type);
    }

    public static function asDependency(DependencyName $name): self
    {
        return new Property(name: $name->value, type: Type::OBJECT);
    }

    /**
     * @param non-empty-string $constant
     */
    public static function asClassConstant(ClassName $className, string $constant, Type $type): self
    {
        return new Property(
            $constant,
            $type,
            new ClassObject($className, true),
        );
    }

    /**
     * @param non-empty-string $caseName
     */
    public static function asEnum(ClassName $enum, string $caseName, Type $type): self
    {
        return self::asClassConstant($enum, $caseName, $type);
    }

    /**
     * @param non-empty-string $name
     */
    public static function asObjectProperty(Property $parent, string $name, Type $type): self
    {
        return new Property($name, $type, $parent);
    }

    public function mightThrow(): bool
    {
        if (null === $this->parent) {
            return false;
        }

        return $this->parent->mightThrow();
    }

    public function getMetadata(): MetadataInterface
    {
        if (null !== $this->parent) {
            return $this->parent->getMetadata();
        }

        $dependencyName = $this->getDependencyName();
        if (null === $dependencyName) {
            return new Metadata();
        }

        return new Metadata(dependencyNames: [$dependencyName]);
    }

    public function getTemplate(): string
    {
        $isDependency = $this->getIsDependency();

        if (null === $this->parent && false === $isDependency) {
            return '${{ name }}';
        }

        if (null === $this->parent && true === $isDependency) {
            return '{{ {{ name }} }}';
        }

        return '{{ parent }}{{ accessor }}{{ name }}';
    }

    public function getContext(): array
    {
        $context = [
            'name' => $this->name,
        ];

        if ($this->parent instanceof ExpressionInterface) {
            $context['parent'] = $this->parent;
            $context['accessor'] = $this->parent->isStatic() ? '::' : '->';
        }

        return $context;
    }

    public function getIsDependency(): bool
    {
        return $this->getDependencyName() instanceof DependencyName;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): array
    {
        return [$this->type];
    }

    private function getDependencyName(): ?DependencyName
    {
        foreach (DependencyName::cases() as $dependencyName) {
            if ($this->name === $dependencyName->value) {
                return $dependencyName;
            }
        }

        return null;
    }
}
