<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Block\TryCatch;

class TryBlock extends AbstractBlock
{
    private const RENDER_TEMPLATE = <<<'EOD'
try {
{{ body }}
}
EOD;

    public function getTemplate(): string
    {
        return self::RENDER_TEMPLATE;
    }

    public function getContext(): array
    {
        return [
            'body' => $this->createResolvableBody(),
        ];
    }
}
