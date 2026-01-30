<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Expression;

use webignition\BasilCompilableSourceFactory\Enum\Type;
use webignition\BasilCompilableSourceFactory\Model\EncapsulateWhenCastingTrait;
use webignition\BasilCompilableSourceFactory\Model\IsNotStaticTrait;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\TypeCollection;

class CastExpression implements ExpressionInterface
{
    use IsNotStaticTrait;
    use EncapsulateWhenCastingTrait;

    public function __construct(
        private ExpressionInterface $expression,
        private Type $castTo,
    ) {}

    public function getTemplate(): string
    {
        $template = '{{ expression }}';

        if ($this->expression->getType()->equals(new TypeCollection([$this->castTo]))) {
            return $template;
        }

        if ($this->expression->encapsulateWhenCasting()) {
            $template = '(' . $template . ')';
        }

        return '({{ cast_type }}) ' . $template;
    }

    public function getContext(): array
    {
        $context = [
            'expression' => $this->expression,
        ];

        if (false === $this->expression->getType()->equals(new TypeCollection([$this->castTo]))) {
            $context['cast_type'] = $this->castTo->value;
        }

        return $context;
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
        return new TypeCollection([$this->castTo]);
    }
}
