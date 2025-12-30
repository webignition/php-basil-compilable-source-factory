<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Expression;

use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilCompilableSourceFactory\Model\VariableName;

readonly class ArrayAccessExpression implements ExpressionInterface
{
    public function __construct(
        private VariableDependency|VariableName $variable,
        private string $key
    ) {}

    public function getTemplate(): string
    {
        return '{{ variable }}[\'{{ key }}\']';
    }

    public function getContext(): array
    {
        return [
            'variable' => $this->variable,
            'key' => $this->key,
        ];
    }

    public function getMetadata(): MetadataInterface
    {
        return $this->variable->getMetadata();
    }
}
