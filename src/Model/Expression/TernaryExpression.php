<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Expression;

use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;

readonly class TernaryExpression implements ExpressionInterface
{
    public function __construct(
        private ExpressionInterface $expression,
        private ExpressionInterface $trueExpression,
        private ExpressionInterface $falseExpression,
    ) {}

    public function getTemplate(): string
    {
        return '{{ expression }} ? {{ true_expression }} : {{ false_expression }}';
    }

    public function getContext(): array
    {
        return [
            'expression' => $this->expression,
            'true_expression' => $this->trueExpression,
            'false_expression' => $this->falseExpression,
        ];
    }

    public function getMetadata(): MetadataInterface
    {
        return new Metadata()
            ->merge($this->expression->getMetadata())
            ->merge($this->trueExpression->getMetadata())
            ->merge($this->falseExpression->getMetadata())
        ;
    }
}
