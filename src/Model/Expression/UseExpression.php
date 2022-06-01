<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Expression;

use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;

class UseExpression implements ExpressionInterface
{
    private const RENDER_TEMPLATE = 'use {{ class_name }}';

    private ClassName $className;

    public function __construct(ClassName $className)
    {
        $this->className = $className;
    }

    public function getTemplate(): string
    {
        return self::RENDER_TEMPLATE;
    }

    public function getContext(): array
    {
        return [
            'class_name' => $this->renderClassName(),
        ];
    }

    public function getMetadata(): MetadataInterface
    {
        return new Metadata();
    }

    private function renderClassName(): string
    {
        $content = $this->className->getClassName();
        $alias = $this->className->getAlias();

        if (is_string($alias)) {
            $content .= ' as ' . $alias;
        }

        return $content;
    }
}
