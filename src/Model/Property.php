<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Model\Expression\ClassObject;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;

class Property implements ExpressionInterface, IsAssigneeInterface
{
    use IsMutableStaticTrait;
    use NeverEncapsulateWhenCastingTrait;

    /**
     * @param non-empty-string $name
     */
    public function __construct(
        private readonly string $name,
        private readonly TypeCollection $type,
        private readonly ?ExpressionInterface $parent = null,
    ) {}

    /**
     * @param non-empty-string $name
     */
    public static function asObjectVariable(string $name): self
    {
        return new Property(name: $name, type: TypeCollection::object());
    }

    /**
     * @param non-empty-string $name
     */
    public static function asStringVariable(string $name): self
    {
        return new Property(name: $name, type: TypeCollection::string());
    }

    /**
     * @param non-empty-string $name
     */
    public static function asBooleanVariable(string $name): self
    {
        return new Property(name: $name, type: TypeCollection::boolean());
    }

    /**
     * @param non-empty-string $name
     */
    public static function asIntegerVariable(string $name): self
    {
        return new Property(name: $name, type: TypeCollection::integer());
    }

    public static function asDependency(DependencyName $name): self
    {
        return new Property(name: $name->value, type: TypeCollection::object());
    }

    /**
     * @param non-empty-string $constant
     */
    public static function asClassConstant(ClassName $className, string $constant, TypeCollection $type): self
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
    public static function asEnum(ClassName $enum, string $caseName, TypeCollection $type): self
    {
        return self::asClassConstant($enum, $caseName, $type);
    }

    /**
     * @param non-empty-string $name
     */
    public static function asObjectProperty(Property $parent, string $name, TypeCollection $type): self
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

    public function getType(): TypeCollection
    {
        return $this->type;
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
