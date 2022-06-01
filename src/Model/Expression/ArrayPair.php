<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Expression;

use webignition\BasilCompilableSourceFactory\Model\HasMetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\StubbleResolvable\ResolvableInterface;

class ArrayPair implements ResolvableInterface, HasMetadataInterface
{
    private const RENDER_TEMPLATE = '{{ key }} => {{ value }},';

    private ArrayKey $key;
    private ExpressionInterface $value;

    public function __construct(ArrayKey $key, ExpressionInterface $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    public function getTemplate(): string
    {
        return self::RENDER_TEMPLATE;
    }

    public function getContext(): array
    {
        return [
            'key' => (string) $this->key,
            'value' => $this->value,
        ];
    }

    public function getMetadata(): MetadataInterface
    {
        return $this->value->getMetadata();
    }
}
