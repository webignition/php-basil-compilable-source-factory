<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Body;

use webignition\BasilCompilableSourceFactory\Model\DeferredResolvableCollectionTrait;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ClosureExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\HasMetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\IsAssigneeInterface;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\Stubble\CollectionItemContext;
use webignition\Stubble\Resolvable\ResolvableCollection;
use webignition\Stubble\Resolvable\ResolvableCollectionInterface;
use webignition\Stubble\Resolvable\ResolvableInterface;
use webignition\Stubble\Resolvable\ResolvedTemplateMutatorResolvable;

class Body implements BodyInterface, ResolvableCollectionInterface
{
    use DeferredResolvableCollectionTrait;

    /**
     * @var BodyContentInterface[]
     */
    private array $content;

    private MetadataInterface $metadata;

    /**
     * @param BodyContentInterface[] $content
     */
    public function __construct(array $content)
    {
        $this->content = $this->filterContent($content);
        $this->metadata = $this->buildMetadata();
    }

    public static function createEnclosingBody(BodyInterface $body): self
    {
        return new Body([
            new Statement(
                new ClosureExpression($body)
            ),
        ]);
    }

    /**
     * @param array<mixed> $expressions
     *
     * @throws \InvalidArgumentException
     */
    public static function createFromExpressions(array $expressions): self
    {
        $statements = [];

        foreach ($expressions as $index => $expression) {
            if ($expression instanceof ExpressionInterface) {
                $statements[] = new Statement($expression);
            } else {
                throw new \InvalidArgumentException('Non-expression at index ' . (string) $index);
            }
        }

        return new Body($statements);
    }

    public static function createForSingleAssignmentStatement(
        IsAssigneeInterface $assignee,
        ExpressionInterface $value
    ): self {
        return new Body([
            new Statement(
                new AssignmentExpression($assignee, $value)
            )
        ]);
    }

    /**
     * @param BodyContentInterface[] $content
     */
    public function withContent(array $content): self
    {
        return new Body(array_merge($this->content, $content));
    }

    public function getMetadata(): MetadataInterface
    {
        return $this->metadata;
    }

    public function mightThrow(): bool
    {
        foreach ($this->content as $content) {
            if ($content->mightThrow()) {
                return true;
            }
        }

        return false;
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

    /**
     * @param BodyContentInterface[] $content
     *
     * @return BodyContentInterface[]
     */
    private function filterContent(array $content): array
    {
        $filteredContent = [];

        foreach ($content as $item) {
            if ($this->includeContent($item)) {
                $filteredContent[] = clone $item;
            }
        }

        return $filteredContent;
    }

    /**
     * @param mixed $item
     */
    private function includeContent($item): bool
    {
        if (!$item instanceof BodyContentInterface) {
            return false;
        }

        if ($item instanceof self && 0 === count($item->content)) {
            return false;
        }

        return true;
    }

    private function buildMetadata(): MetadataInterface
    {
        $metadata = new Metadata();

        foreach ($this->content as $item) {
            if ($item instanceof HasMetadataInterface) {
                $metadata = $metadata->merge($item->getMetadata());
            }
        }

        return $metadata;
    }
}
