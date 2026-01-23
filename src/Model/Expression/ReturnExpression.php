<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Expression;

use webignition\BasilCompilableSourceFactory\Model\Construct\ReturnConstruct;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;

class ReturnExpression implements ExpressionInterface
{
    private const string RENDER_TEMPLATE = '{{ return_construct }} {{ expression_content }}';

    private ExpressionInterface $expression;

    public function __construct(ExpressionInterface $expression)
    {
        $this->expression = $expression;
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
            'return_construct' => (string) (new ReturnConstruct()),
            'expression_content' => $this->expression,
        ];
    }
}
