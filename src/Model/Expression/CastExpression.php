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

    private const string RENDER_TEMPLATE = '({{ cast_type }}) {{ expression }}';

    public function __construct(
        private ExpressionInterface $expression,
        private Type $castTo,
    ) {}

    public function getTemplate(): string
    {
        return self::RENDER_TEMPLATE;
    }

    public function getContext(): array
    {
        return [
            'cast_type' => $this->castTo->value,
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
        return new TypeCollection([$this->castTo]);
    }
}
