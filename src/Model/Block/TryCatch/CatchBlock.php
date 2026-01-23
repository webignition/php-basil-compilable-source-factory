<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Block\TryCatch;

use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\CatchExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;

class CatchBlock extends AbstractBlock
{
    private const RENDER_TEMPLATE = <<<'EOD'
catch ({{ catch_expression }}) {
{{ body }}
}
EOD;

    private CatchExpression $catchExpression;

    public function __construct(CatchExpression $catchExpression, BodyInterface $body)
    {
        parent::__construct($body);

        $this->catchExpression = $catchExpression;
    }

    public function getTemplate(): string
    {
        return self::RENDER_TEMPLATE;
    }

    public function getContext(): array
    {
        return [
            'catch_expression' => $this->catchExpression,
            'body' => $this->createResolvableBody(),
        ];
    }

    public function getMetadata(): MetadataInterface
    {
        $metadata = parent::getMetadata();

        return $metadata->merge($this->catchExpression->getMetadata());
    }
}
