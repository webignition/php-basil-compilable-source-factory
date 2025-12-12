<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\TypeDeclaration;

use webignition\BasilCompilableSourceFactory\Model\DeferredResolvableCollectionTrait;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\Stubble\CollectionItemContext;
use webignition\Stubble\Resolvable\ResolvableCollection;
use webignition\Stubble\Resolvable\ResolvableCollectionInterface;
use webignition\Stubble\Resolvable\ResolvableInterface;
use webignition\Stubble\Resolvable\ResolvableWithoutContext;
use webignition\Stubble\Resolvable\ResolvedTemplateMutationInterface;
use webignition\Stubble\Resolvable\ResolvedTemplateMutatorResolvable;

class ObjectTypeDeclarationCollection implements
    TypeDeclarationCollectionInterface,
    ResolvedTemplateMutationInterface,
    ResolvableCollectionInterface
{
    use DeferredResolvableCollectionTrait;

    /**
     * @var ObjectTypeDeclaration[]
     */
    private array $declarations;

    /**
     * @param ObjectTypeDeclaration[] $declarations
     */
    public function __construct(array $declarations)
    {
        $this->declarations = $declarations;
    }

    public function getMetadata(): MetadataInterface
    {
        $metadata = Metadata::create();

        foreach ($this->declarations as $declaration) {
            $metadata = $metadata->merge($declaration->getMetadata());
        }

        return $metadata;
    }

    /**
     * @return callable[]
     */
    public function getResolvedTemplateMutators(): array
    {
        return [
            function (string $resolvedTemplate): string {
                return $this->resolvedTemplateMutator($resolvedTemplate);
            },
        ];
    }

    protected function createResolvable(): ResolvableInterface
    {
        $resolvableDeclarations = [];
        foreach ($this->declarations as $declaration) {
            $resolvableDeclarations[] = new ResolvedTemplateMutatorResolvable(
                new ResolvableWithoutContext((string) $declaration),
                function (string $resolvedTemplate, ?CollectionItemContext $context) {
                    return $this->declarationResolvedTemplateMutator($resolvedTemplate, $context);
                }
            );
        }

        return ResolvableCollection::create($resolvableDeclarations);
    }

    private function resolvedTemplateMutator(string $resolvedTemplate): string
    {
        $parts = explode(' | ', $resolvedTemplate);
        $parts = array_filter($parts);

        $namespaceSeparator = '\\';
        usort($parts, function (string $a, string $b) use ($namespaceSeparator) {
            $a = ltrim($a, $namespaceSeparator);
            $b = ltrim($b, $namespaceSeparator);

            if ($a === $b) {
                return 0;
            }

            return $a < $b ? -1 : 1;
        });

        return implode(' | ', $parts);
    }

    private function declarationResolvedTemplateMutator(
        string $resolvedTemplate,
        ?CollectionItemContext $context
    ): string {
        $appendLeadingSpace = $context instanceof CollectionItemContext && false === $context->isFirst();
        if ($appendLeadingSpace) {
            $resolvedTemplate = ' ' . $resolvedTemplate;
        }

        $appendSeparator = $context instanceof CollectionItemContext && false === $context->isLast();
        if ($appendSeparator) {
            $resolvedTemplate .= ' |';
        }

        return $resolvedTemplate;
    }
}
