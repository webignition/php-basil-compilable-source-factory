<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

use webignition\BasilCompilableSourceFactory\Enum\VariableName;
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
        private readonly ?ExpressionInterface $parent = null,
        private readonly bool $isDependency = false,
    ) {}

    /**
     * @param non-empty-string $name
     */
    public static function asVariable(string $name): self
    {
        return new Property($name);
    }

    public static function asDependency(VariableName $name): self
    {
        return new Property($name->value)->setIsDependency();
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
        if (null === $this->parent) {
            return $this->isDependency
                ? new Metadata(variableNames: [$this->name])
                : new Metadata();
        }

        return $this->parent->getMetadata();
    }

    public function getTemplate(): string
    {
        if (null === $this->parent && false === $this->isDependency) {
            return '${{ name }}';
        }

        if (null === $this->parent && true === $this->isDependency) {
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

    public function setIsDependency(): Property
    {
        return new Property(
            $this->name,
            $this->parent,
            true,
        );
    }

    public function getIsDependency(): bool
    {
        return $this->isDependency;
    }
}
