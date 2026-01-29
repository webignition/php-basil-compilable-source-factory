<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Expression;

use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\IndentTrait;
use webignition\BasilCompilableSourceFactory\Model\IsNotStaticTrait;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\TypeCollection;
use webignition\Stubble\Resolvable\ResolvedTemplateMutatorResolvable;

readonly class ClosureExpression implements ExpressionInterface
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
        private TypeCollection $type,
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
        return $this->type;
    }
}
