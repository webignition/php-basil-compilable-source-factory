<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Expression;

use webignition\BasilCompilableSourceFactory\Model\DeferredResolvableCreationTrait;
use webignition\BasilCompilableSourceFactory\Model\IsNotStaticTrait;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\TypeCollection;
use webignition\Stubble\Resolvable\ResolvableCollection;
use webignition\Stubble\Resolvable\ResolvableInterface;

class CompositeExpression implements ExpressionInterface
{
    use DeferredResolvableCreationTrait;
    use IsNotStaticTrait;

    /**
     * @var ExpressionInterface[]
     */
    private $expressions;

    private TypeCollection $type;

    /**
     * @param array<mixed> $expressions
     */
    public function __construct(array $expressions, TypeCollection $type)
    {
        $this->expressions = array_filter($expressions, function ($item) {
            return $item instanceof ExpressionInterface;
        });

        $metadata = new Metadata();
        foreach ($this->expressions as $expression) {
            $metadata = $metadata->merge($expression->getMetadata());
        }

        $this->type = $type;
    }

    public function getMetadata(): MetadataInterface
    {
        $metadata = new Metadata();
        foreach ($this->expressions as $expression) {
            $metadata = $metadata->merge($expression->getMetadata());
        }

        return $metadata;
    }

    public function mightThrow(): bool
    {
        foreach ($this->expressions as $expression) {
            if ($expression->mightThrow()) {
                return true;
            }
        }

        return false;
    }

    public function getType(): TypeCollection
    {
        return $this->type;
    }

    protected function createResolvable(): ResolvableInterface
    {
        if (null === $this->resolvable) {
            $this->resolvable = ResolvableCollection::create($this->expressions);
        }

        return $this->resolvable;
    }
}
