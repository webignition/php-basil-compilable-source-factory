<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Expression\ArrayExpression;

use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\HasMetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\Stubble\Resolvable\ResolvableInterface;

class ArrayPair implements ResolvableInterface, HasMetadataInterface
{
    private const RENDER_TEMPLATE = '{{ key }} => {{ value }},';

    private string $key;
    private ExpressionInterface $value;

    public function __construct(string $key, ExpressionInterface $value)
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
            'key' => '\'' . $this->key . '\'',
            'value' => $this->value,
        ];
    }

    public function getMetadata(): MetadataInterface
    {
        return $this->value->getMetadata();
    }

    public function getValue(): ExpressionInterface
    {
        return $this->value;
    }
}
