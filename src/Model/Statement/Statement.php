<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Statement;

use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\Stubble\Resolvable\ResolvableInterface;

class Statement implements ResolvableInterface, StatementInterface
{
    private const RENDER_TEMPLATE = '{{ expression }};';

    private ExpressionInterface $expression;

    public function __construct(ExpressionInterface $expression)
    {
        $this->expression = $expression;
    }

    public function getExpression(): ExpressionInterface
    {
        return $this->expression;
    }

    public function getMetadata(): MetadataInterface
    {
        return $this->expression->getMetadata();
    }

    public function getTemplate(): string
    {
        return self::RENDER_TEMPLATE;
    }

    public function getContext(): array
    {
        return [
            'expression' => $this->expression,
        ];
    }

    public function mightThrow(): bool
    {
        return $this->expression->mightThrow();
    }
}
