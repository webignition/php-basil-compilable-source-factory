<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\Stubble\CollectionItemContext;
use webignition\Stubble\Resolvable\ResolvableCollection;
use webignition\Stubble\Resolvable\ResolvableCollectionInterface;
use webignition\Stubble\Resolvable\ResolvableInterface;
use webignition\Stubble\Resolvable\ResolvedTemplateMutatorResolvable;

class ClassBody implements ResolvableInterface, ResolvableCollectionInterface
{
    use DeferredResolvableCollectionTrait;

    /**
     * @var MethodDefinitionInterface[]
     */
    private array $methods;

    /**
     * @param MethodDefinitionInterface[] $methods
     */
    public function __construct(array $methods)
    {
        $this->methods = $methods;
    }

    public function getMetadata(): MetadataInterface
    {
        $metadata = Metadata::create();

        foreach ($this->methods as $method) {
            $metadata = $metadata->merge($method->getMetadata());
        }

        return $metadata;
    }

    protected function createResolvable(): ResolvableInterface
    {
        $resolvables = [];

        foreach ($this->methods as $method) {
            $resolvables[] = new ResolvedTemplateMutatorResolvable(
                $method,
                function (string $resolvedTemplate, ?CollectionItemContext $context): string {
                    return $this->methodResolvedTemplateMutator($resolvedTemplate, $context);
                }
            );
        }

        return ResolvableCollection::create($resolvables);
    }

    private function methodResolvedTemplateMutator(string $resolvedTemplate, ?CollectionItemContext $context): string
    {
        $appendNewLine = $context instanceof CollectionItemContext && false === $context->isLast();
        if ($appendNewLine) {
            $resolvedTemplate .= "\n\n";
        }

        return $resolvedTemplate;
    }
}
