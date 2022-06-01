<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Block\IfBlock;

use webignition\BasilCompilableSourceFactory\Model\Block\AbstractBlock;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;

class IfBlock extends AbstractBlock implements BodyInterface
{
    private const RENDER_TEMPLATE = <<<'EOD'
if ({{ expression }}) {
{{ body }}
}
EOD;

    private ExpressionInterface $expression;

    public function __construct(ExpressionInterface $expression, BodyInterface $body)
    {
        parent::__construct($body);

        $this->expression = $expression;
    }

    public function getMetadata(): MetadataInterface
    {
        $metadata = $this->expression->getMetadata();

        return $metadata->merge(parent::getMetadata());
    }

    public function getTemplate(): string
    {
        return self::RENDER_TEMPLATE;
    }

    public function getContext(): array
    {
        return [
            'expression' => $this->expression,
            'body' => $this->createResolvableBody(),
        ];
    }
}
