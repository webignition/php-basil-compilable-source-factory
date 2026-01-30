<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Expression;

use webignition\BasilCompilableSourceFactory\Model\HasReturnTypeInterface;
use webignition\BasilCompilableSourceFactory\Model\IsNotStaticTrait;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\ReturnableInterface;
use webignition\BasilCompilableSourceFactory\Model\TypeCollection;

class EncapsulatedExpression implements ExpressionInterface, HasReturnTypeInterface, ReturnableInterface
{
    use IsNotStaticTrait;

    private const RENDER_TEMPLATE = '({{ expression }})';

    private ExpressionInterface $expression;

    public function __construct(ExpressionInterface $expression)
    {
        $this->expression = $expression;
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

    public function getMetadata(): MetadataInterface
    {
        return $this->expression->getMetadata();
    }

    public function mightThrow(): bool
    {
        return $this->expression->mightThrow();
    }

    public function getType(): TypeCollection
    {
        return $this->expression->getType();
    }

    public function getReturnType(): ?TypeCollection
    {
        if ($this->expression instanceof ReturnableInterface && $this->expression instanceof HasReturnTypeInterface) {
            return $this->expression->getReturnType();
        }

        return null;
    }
}
