<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Attribute;

use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;

class Attribute implements AttributeInterface
{
    private const string RENDER_TEMPLATE_WITH_ARGUMENTS = '#[{{ name }}({{ arguments }})]';
    private const string RENDER_TEMPLATE_WITHOUT_ARGUMENTS = '#[{{ name }}]';

    private ClassName $className;

    /**
     * @var string[]
     */
    private array $arguments;

    private MetadataInterface $metadata;

    /**
     * @param string[] $arguments
     */
    public function __construct(ClassName $className, array $arguments = [])
    {
        $this->className = $className;
        $this->arguments = $arguments;

        $this->metadata = new Metadata(classNames: [$this->className->getClassName()]);
    }

    public function getTemplate(): string
    {
        return [] === $this->arguments ? self::RENDER_TEMPLATE_WITHOUT_ARGUMENTS : self::RENDER_TEMPLATE_WITH_ARGUMENTS;
    }

    public function getContext(): array
    {
        return [
            'name' => $this->className->getClass(),
            'arguments' => implode(', ', $this->arguments)
        ];
    }

    public function getMetadata(): MetadataInterface
    {
        return $this->metadata;
    }
}
