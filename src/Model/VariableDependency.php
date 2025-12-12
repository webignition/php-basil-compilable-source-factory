<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\VariableNames;

class VariableDependency implements ExpressionInterface, VariableDependencyInterface
{
    private const RENDER_TEMPLATE = '{{ {{ name }} }}';

    /**
     * @param VariableNames::* $name
     */
    public function __construct(
        private readonly string $name
    ) {}

    /**
     * @return VariableNames::*
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function getMetadata(): MetadataInterface
    {
        $placeholderCollection = new VariableDependencyCollection();
        $placeholderCollection->add($this);

        return new Metadata([
            Metadata::KEY_VARIABLE_DEPENDENCIES => $placeholderCollection,
        ]);
    }

    public function getTemplate(): string
    {
        return self::RENDER_TEMPLATE;
    }

    public function getContext(): array
    {
        return [
            'name' => $this->name,
        ];
    }
}
