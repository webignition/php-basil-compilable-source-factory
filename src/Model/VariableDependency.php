<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

use webignition\BasilCompilableSourceFactory\Enum\VariableName as VariableNameEnum;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;

class VariableDependency implements ExpressionInterface, VariableDependencyInterface, IsAssigneeInterface
{
    private const RENDER_TEMPLATE = '{{ {{ name }} }}';

    public function __construct(
        private readonly VariableNameEnum $name
    ) {}

    public function getName(): string
    {
        return $this->name->value;
    }

    public function getMetadata(): MetadataInterface
    {
        return new Metadata(variableNames: [$this->name]);
    }

    public function getTemplate(): string
    {
        return self::RENDER_TEMPLATE;
    }

    public function getContext(): array
    {
        return [
            'name' => $this->getName(),
        ];
    }
}
