<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

use webignition\BasilCompilableSourceFactory\Model\Attribute\AttributeCollection;
use webignition\BasilCompilableSourceFactory\Model\Attribute\AttributeInterface;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\Stubble\Resolvable\ResolvedTemplateMutatorResolvable;

class MethodDefinition implements MethodDefinitionInterface
{
    use IndentTrait;

    public const VISIBILITY_PUBLIC = 'public';
    public const VISIBILITY_PROTECTED = 'protected';
    public const VISIBILITY_PRIVATE = 'private';
    private const string RENDER_TEMPLATE_ATTRIBUTE_COLLECTION_COMPONENT = '{{ attributes }}';
    private const string RENDER_TEMPLATE_SIGNATURE_AND_BODY_COMPONENT = <<<'EOD'
        {{ signature }}
        {
        {{ body }}
        }
        EOD;

    private string $visibility;

    private ?string $returnType;
    private string $name;
    private BodyInterface $body;

    /**
     * @var string[]
     */
    private array $arguments;

    private bool $isStatic;

    private AttributeCollection $attributes;

    /**
     * @param string[] $arguments
     */
    public function __construct(string $name, BodyInterface $body, array $arguments = [])
    {
        $this->visibility = self::VISIBILITY_PUBLIC;
        $this->returnType = null;
        $this->name = $name;
        $this->body = $body;
        $this->arguments = $arguments;
        $this->isStatic = false;
        $this->attributes = new AttributeCollection();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMetadata(): MetadataInterface
    {
        return Metadata::create()
            ->merge($this->attributes->getMetadata())
            ->merge($this->body->getMetadata())
        ;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function setPublic(): void
    {
        $this->visibility = self::VISIBILITY_PUBLIC;
    }

    public function setProtected(): void
    {
        $this->visibility = self::VISIBILITY_PROTECTED;
    }

    public function setPrivate(): void
    {
        $this->visibility = self::VISIBILITY_PRIVATE;
    }

    public function getReturnType(): ?string
    {
        return $this->returnType;
    }

    public function setReturnType(?string $returnType): void
    {
        $this->returnType = $returnType;
    }

    public function setStatic(): void
    {
        $this->isStatic = true;
    }

    public function isStatic(): bool
    {
        return $this->isStatic;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function withAttribute(AttributeInterface $attribute): static
    {
        $new = clone $this;
        $new->attributes = $this->attributes->add($attribute);

        return $new;
    }

    public function getTemplate(): string
    {
        $template = self::RENDER_TEMPLATE_SIGNATURE_AND_BODY_COMPONENT;

        if (0 !== count($this->attributes)) {
            $template = self::RENDER_TEMPLATE_ATTRIBUTE_COLLECTION_COMPONENT . "\n" . $template;
        }

        return $template;
    }

    public function getContext(): array
    {
        return [
            'attributes' => count($this->attributes) > 0 ? $this->attributes : '',
            'signature' => $this->createSignature(),
            'body' => new ResolvedTemplateMutatorResolvable(
                $this->body,
                function (string $resolvedTemplate): string {
                    return rtrim($this->indent($resolvedTemplate));
                }
            ),
        ];
    }

    private function createSignature(): string
    {
        $signature = $this->getVisibility() . ' ';

        if ($this->isStatic()) {
            $signature .= 'static ';
        }

        $arguments = $this->createSignatureArguments($this->getArguments());
        $signature .= 'function ' . $this->getName() . '(' . $arguments . ')';

        $returnType = $this->getReturnType();

        if (null !== $returnType) {
            $signature .= ': ' . $returnType;
        }

        return $signature;
    }

    /**
     * @param string[] $argumentNames
     */
    private function createSignatureArguments(array $argumentNames): string
    {
        $arguments = $argumentNames;

        array_walk($arguments, function (&$argument) {
            $argument = '$' . $argument;
        });

        return implode(', ', $arguments);
    }
}
