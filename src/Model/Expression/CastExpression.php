<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Expression;

use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;

class CastExpression implements ExpressionInterface
{
    private const string RENDER_TEMPLATE = '({{ cast_type }}) {{ expression }}';

    public function __construct(
        private ExpressionInterface $expression,
        private string $castTo
    ) {}

    public function getTemplate(): string
    {
        return self::RENDER_TEMPLATE;
    }

    public function getContext(): array
    {
        return [
            'cast_type' => $this->castTo,
            'expression' => $this->expression,
        ];
    }

    public function getMetadata(): MetadataInterface
    {
        return $this->expression->getMetadata();
    }

    public function mightThrow(): bool
    {
        return $this->expression->mightThrow();
    }
}
