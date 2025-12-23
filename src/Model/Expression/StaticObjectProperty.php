<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Expression;

use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;

readonly class StaticObjectProperty implements ExpressionInterface
{
    private const RENDER_TEMPLATE = '{{ object }}::{{ property }}';

    public function __construct(
        private ExpressionInterface $object,
        private string $propertyName,
    ) {}

    public function getTemplate(): string
    {
        return self::RENDER_TEMPLATE;
    }

    public function getContext(): array
    {
        return [
            'object' => $this->object,
            'property' => $this->propertyName,
        ];
    }

    public function getMetadata(): MetadataInterface
    {
        return new Metadata();
    }
}
