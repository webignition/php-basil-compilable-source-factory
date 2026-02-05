<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Attribute;

use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;

class Attribute implements AttributeInterface
{
    private const string RENDER_TEMPLATE_WITH_ARGUMENTS = '#[{{ name }}({{ arguments }})]';
    private const string RENDER_TEMPLATE_WITHOUT_ARGUMENTS = '#[{{ name }}]';

    private ClassName $className;

    private ?MethodArgumentsInterface $arguments;

    private MetadataInterface $metadata;

    public function __construct(ClassName $className, ?MethodArgumentsInterface $arguments = null)
    {
        $this->className = $className;
        $this->arguments = $arguments;

        $this->metadata = new Metadata(classNames: [$this->className->getClassName()]);
    }

    public function getTemplate(): string
    {
        return null === $this->arguments
            ? self::RENDER_TEMPLATE_WITHOUT_ARGUMENTS
            : self::RENDER_TEMPLATE_WITH_ARGUMENTS;
    }

    public function getContext(): array
    {
        $context = [
            'name' => (string) $this->className,
        ];

        if ($this->arguments instanceof MethodArgumentsInterface) {
            $context['arguments'] = $this->arguments;
        }

        return $context;
    }

    public function getMetadata(): MetadataInterface
    {
        return $this->metadata;
    }
}
