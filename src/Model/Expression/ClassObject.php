<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Expression;

use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\NeverThrowsTrait;
use webignition\BasilCompilableSourceFactory\Model\TypeCollection;

readonly class ClassObject implements ExpressionInterface
{
    use NeverThrowsTrait;

    public function __construct(
        private ClassName $className,
        private bool $isStatic = false,
    ) {}

    public function getTemplate(): string
    {
        return '{{ class }}';
    }

    public function getContext(): array
    {
        return [
            'class' => $this->className->renderClassName(),
        ];
    }

    public function getMetadata(): MetadataInterface
    {
        return new Metadata([
            $this->className->getClassName(),
        ]);
    }

    public function isStatic(): bool
    {
        return $this->isStatic;
    }

    public function getType(): TypeCollection
    {
        return TypeCollection::object();
    }
}
