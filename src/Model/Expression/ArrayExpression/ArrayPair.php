<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Expression\ArrayExpression;

use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\HasMetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\Stubble\Resolvable\ResolvableInterface;

class ArrayPair implements ResolvableInterface, HasMetadataInterface
{
    private string $key;
    private ExpressionInterface $value;

    private bool $renderKey;

    public function __construct(string $key, ExpressionInterface $value, bool $renderKey = true)
    {
        $this->key = $key;
        $this->value = $value;
        $this->renderKey = $renderKey;
    }

    public function getTemplate(): string
    {
        $template = '{{ value }},';

        if ($this->renderKey) {
            $template = '{{ key }} => ' . $template;
        }

        return $template;
    }

    public function getContext(): array
    {
        $context = [
            'value' => $this->value,
        ];

        if ($this->renderKey) {
            $context['key'] = '\'' . $this->key . '\'';
        }

        return $context;
    }

    public function getMetadata(): MetadataInterface
    {
        return $this->value->getMetadata();
    }

    public function getValue(): ExpressionInterface
    {
        return $this->value;
    }

    public function withoutRenderKey(): self
    {
        $new = clone $this;
        $new->renderKey = false;

        return $new;
    }
}
