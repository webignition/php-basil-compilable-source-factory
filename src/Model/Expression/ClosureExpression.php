<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Expression;

use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\IndentTrait;
use webignition\BasilCompilableSourceFactory\Model\IsNotStaticTrait;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\Stubble\Resolvable\ResolvedTemplateMutatorResolvable;

class ClosureExpression implements ExpressionInterface
{
    use IndentTrait;
    use IsNotStaticTrait;

    private const RENDER_TEMPLATE = <<<'EOD'
(function () {
{{ body }}
})()
EOD;

    private BodyInterface $body;

    public function __construct(BodyInterface $body)
    {
        $this->body = $body;
    }

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
}
