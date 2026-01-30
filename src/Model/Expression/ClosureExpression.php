<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Expression;

use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\HasReturnTypeInterface;
use webignition\BasilCompilableSourceFactory\Model\IndentTrait;
use webignition\BasilCompilableSourceFactory\Model\IsNotStaticTrait;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\ReturnableInterface;
use webignition\BasilCompilableSourceFactory\Model\TypeCollection;
use webignition\Stubble\Resolvable\ResolvedTemplateMutatorResolvable;

readonly class ClosureExpression implements ExpressionInterface, HasReturnTypeInterface, ReturnableInterface
{
    use IndentTrait;
    use IsNotStaticTrait;

    private const RENDER_TEMPLATE = <<<'EOD'
(function () {
{{ body }}
})()
EOD;

    public function __construct(
        private BodyInterface $body,
    ) {}

    public function getMetadata(): MetadataInterface
    {
        return $this->body->getMetadata();
    }

    public function getTemplate(): string
    {
        return self::RENDER_TEMPLATE;
    }

    public function getContext(): array
    {
        return [
            'body' => new ResolvedTemplateMutatorResolvable(
                $this->body,
                function (string $resolvedTemplate): string {
                    return rtrim($this->indent($resolvedTemplate));
                }
            ),
        ];
    }

    public function mightThrow(): bool
    {
        return $this->body->mightThrow();
    }

    public function getType(): TypeCollection
    {
        $returnType = $this->getReturnType();

        return null === $returnType ? TypeCollection::void() : $returnType;
    }

    public function hasReturnType(): bool
    {
        return null !== $this->getReturnType();
    }

    public function getReturnType(): ?TypeCollection
    {
        return $this->body->getReturnType();
    }
}
