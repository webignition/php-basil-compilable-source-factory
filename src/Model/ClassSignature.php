<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

use webignition\StubbleResolvable\ResolvableInterface;

class ClassSignature implements ResolvableInterface
{
    private const RENDER_TEMPLATE_WITHOUT_BASE_CLASS = 'class {{ name }}';
    private const RENDER_TEMPLATE = self::RENDER_TEMPLATE_WITHOUT_BASE_CLASS . ' extends {{ base_class }}';

    /**
     * @param non-empty-string $name
     */
    public function __construct(
        private readonly string $name,
        private readonly ?ClassName $baseClass = null
    ) {
    }

    /**
     * @return non-empty-string
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function getBaseClass(): ?ClassName
    {
        return $this->baseClass;
    }

    public function getTemplate(): string
    {
        return $this->baseClass instanceof ClassName
            ? self::RENDER_TEMPLATE
            : self::RENDER_TEMPLATE_WITHOUT_BASE_CLASS;
    }

    public function getContext(): array
    {
        return [
            'name' => $this->getName(),
            'base_class' => $this->baseClass instanceof ClassName ? $this->baseClass->renderClassName() : '',
        ];
    }
}
