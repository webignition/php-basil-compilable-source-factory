<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Expression;

use webignition\BasilCompilableSourceFactory\Model\IsNotStaticTrait;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\NeverEncapsulateWhenCastingTrait;
use webignition\BasilCompilableSourceFactory\Model\NeverThrowsTrait;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Model\TypeCollection;

readonly class ArrayAccessExpression implements NullableExpressionInterface
{
    use NeverThrowsTrait;
    use IsNotStaticTrait;
    use NeverEncapsulateWhenCastingTrait;

    public function __construct(
        private Property $variable,
        private string $key,
        private TypeCollection $type,
    ) {}

    public function getTemplate(): string
    {
        return '{{ variable }}[\'{{ key }}\']';
    }

    public function getContext(): array
    {
        return [
            'variable' => $this->variable,
            'key' => $this->key,
        ];
    }

    public function getMetadata(): MetadataInterface
    {
        return $this->variable->getMetadata();
    }

    public function getType(): TypeCollection
    {
        return $this->type;
    }
}
