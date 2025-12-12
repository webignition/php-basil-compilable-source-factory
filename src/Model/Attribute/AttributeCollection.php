<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Attribute;

use webignition\BasilCompilableSourceFactory\Model\DeferredResolvableCollectionTrait;
use webignition\BasilCompilableSourceFactory\Model\HasMetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\Stubble\CollectionItemContext;
use webignition\Stubble\Resolvable\ResolvableCollection;
use webignition\Stubble\Resolvable\ResolvableCollectionInterface;
use webignition\Stubble\Resolvable\ResolvableInterface;
use webignition\Stubble\Resolvable\ResolvedTemplateMutatorResolvable;

class AttributeCollection implements HasMetadataInterface, ResolvableCollectionInterface
{
    use DeferredResolvableCollectionTrait;

    /**
     * @var AttributeInterface[]
     */
    private array $attributes = [];

    public function add(AttributeInterface $attribute): self
    {
        $new = clone $this;
        $new->attributes[] = $attribute;

        return $new;
    }

    public function getMetadata(): MetadataInterface
    {
        $metadata = Metadata::create();

        foreach ($this->attributes as $attribute) {
            $metadata = $metadata->merge($attribute->getMetadata());
        }

        return $metadata;
    }

    protected function createResolvable(): ResolvableInterface
    {
        $resolveables = [];

        foreach ($this->attributes as $attribute) {
            $resolveables[] = new ResolvedTemplateMutatorResolvable(
                $attribute,
                function (string $resolvedTemplate, ?CollectionItemContext $context): string {
                    return $this->resolvedItemTemplateMutator($resolvedTemplate, $context);
                }
            );
        }

        return ResolvableCollection::create($resolveables);
    }

    private function resolvedItemTemplateMutator(string $resolvedTemplate, ?CollectionItemContext $context): string
    {
        $appendNewLine = $context instanceof CollectionItemContext && false === $context->isLast();
        if ($appendNewLine) {
            $resolvedTemplate .= "\n";
        }

        return $resolvedTemplate;
    }
}
