<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Block;

use webignition\BasilCompilableSourceFactory\Model\ClassNameCollection;
use webignition\BasilCompilableSourceFactory\Model\DeferredResolvableCollectionTrait;
use webignition\BasilCompilableSourceFactory\Model\Expression\UseExpression;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\Stubble\CollectionItemContext;
use webignition\Stubble\Resolvable\ResolvableCollection;
use webignition\Stubble\Resolvable\ResolvableCollectionInterface;
use webignition\Stubble\Resolvable\ResolvableInterface;
use webignition\Stubble\Resolvable\ResolvedTemplateMutationInterface;
use webignition\Stubble\Resolvable\ResolvedTemplateMutatorResolvable;

class ClassDependencyCollection implements
    \Countable,
    ResolvableInterface,
    ResolvedTemplateMutationInterface,
    ResolvableCollectionInterface
{
    use DeferredResolvableCollectionTrait;

    public function __construct(
        private ClassNameCollection $classNames,
    ) {}

    public function merge(ClassDependencyCollection $collection): ClassDependencyCollection
    {
        return new ClassDependencyCollection($this->classNames->merge($collection->classNames));
    }

    public function count(): int
    {
        return count($this->classNames);
    }

    public function isEmpty(): bool
    {
        return 0 === $this->count();
    }

    /**
     * @return callable[]
     */
    public function getResolvedTemplateMutators(): array
    {
        return [
            function (string $resolvedTemplate): string {
                $lines = explode("\n", $resolvedTemplate);
                sort($lines);

                return implode("\n", $lines);
            },
        ];
    }

    public function getClassNames(): ClassNameCollection
    {
        return $this->classNames;
    }

    protected function createResolvable(): ResolvableInterface
    {
        $useStatementResolvables = [];
        foreach ($this->classNames as $className) {
            $useStatement = new Statement(new UseExpression($className));

            $useStatementResolvables[] = new ResolvedTemplateMutatorResolvable(
                $useStatement,
                function (string $resolvedTemplate, ?CollectionItemContext $context) {
                    return $this->useStatementResolvedTemplateMutator($resolvedTemplate, $context);
                }
            );
        }

        return ResolvableCollection::create($useStatementResolvables);
    }

    private function useStatementResolvedTemplateMutator(
        string $resolvedTemplate,
        ?CollectionItemContext $context
    ): string {
        $appendNewLine = $context instanceof CollectionItemContext && false === $context->isLast();
        if ($appendNewLine) {
            $resolvedTemplate .= "\n";
        }

        return $resolvedTemplate;
    }
}
