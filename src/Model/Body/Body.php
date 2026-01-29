<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Body;

use webignition\BasilCompilableSourceFactory\Model\DeferredResolvableCollectionTrait;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\Stubble\CollectionItemContext;
use webignition\Stubble\Resolvable\ResolvableCollection;
use webignition\Stubble\Resolvable\ResolvableCollectionInterface;
use webignition\Stubble\Resolvable\ResolvableInterface;
use webignition\Stubble\Resolvable\ResolvedTemplateMutatorResolvable;

class Body implements BodyInterface, ResolvableCollectionInterface
{
    use DeferredResolvableCollectionTrait;

    private BodyContentCollection $content;

    public function __construct(?BodyContentCollection $content = null)
    {
        $this->content = $content ?? new BodyContentCollection();
    }

    public function getMetadata(): MetadataInterface
    {
        return $this->content->getMetadata();
    }

    public function mightThrow(): bool
    {
        return $this->content->mightThrow();
    }

    protected function createResolvable(): ResolvableInterface
    {
        $resolvables = [];

        foreach ($this->content as $item) {
            $resolvables[] = new ResolvedTemplateMutatorResolvable(
                $item,
                function (string $resolvedTemplate, ?CollectionItemContext $context): string {
                    return $this->resolvedItemTemplateMutator($resolvedTemplate, $context);
                }
            );
        }

        return ResolvableCollection::create($resolvables);
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
