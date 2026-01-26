<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Expression;

use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\IsNotStaticTrait;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\NeverThrowsTrait;

readonly class ObjectConstant implements ExpressionInterface
{
    use NeverThrowsTrait;
    use IsNotStaticTrait;

    private const RENDER_TEMPLATE = '{{ class }}::{{ property }}';

    public function __construct(
        private ClassName $className,
        private string $property,
    ) {}

    public function getTemplate(): string
    {
        return self::RENDER_TEMPLATE;
    }

    public function getContext(): array
    {
        return [
            'class' => $this->className->renderClassName(),
            'property' => $this->property,
        ];
    }

    public function getMetadata(): MetadataInterface
    {
        return new Metadata([
            $this->className->getClassName(),
        ]);
    }
}
