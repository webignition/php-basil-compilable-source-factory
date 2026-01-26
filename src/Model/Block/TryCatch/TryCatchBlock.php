<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Block\TryCatch;

use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\DeferredResolvableCreationTrait;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\Stubble\Resolvable\ResolvableCollection;
use webignition\Stubble\Resolvable\ResolvableInterface;
use webignition\Stubble\Resolvable\ResolvedTemplateMutatorResolvable;

class TryCatchBlock implements BodyInterface
{
    use DeferredResolvableCreationTrait;

    private TryBlock $tryBlock;

    /**
     * @var CatchBlock[]
     */
    private array $catchBlocks;

    private MetadataInterface $metadata;

    public function __construct(TryBlock $tryBlock, CatchBlock ...$catchBlocks)
    {
        $this->tryBlock = $tryBlock;
        $this->catchBlocks = $catchBlocks;
        $this->metadata = $this->buildMetadata();
    }

    public function getMetadata(): MetadataInterface
    {
        return $this->metadata;
    }

    public function mightThrow(): bool
    {
        if ($this->tryBlock->mightThrow()) {
            return true;
        }

        foreach ($this->catchBlocks as $catchBlock) {
            if ($catchBlock->mightThrow()) {
                return true;
            }
        }

        return false;
    }

    protected function createResolvable(): ResolvableInterface
    {
        $resolvableItems = [
            $this->tryBlock,
        ];

        foreach ($this->catchBlocks as $catchBlock) {
            $resolvableItems[] = new ResolvedTemplateMutatorResolvable(
                $catchBlock,
                function (string $resolvedTemplate) {
                    return $this->catchBlockResolvedTemplateMutator($resolvedTemplate);
                }
            );
        }

        return ResolvableCollection::create($resolvableItems);
    }

    private function buildMetadata(): MetadataInterface
    {
        $metadata = new Metadata();
        $metadata = $metadata->merge($this->tryBlock->getMetadata());

        foreach ($this->catchBlocks as $catchBlock) {
            $metadata = $metadata->merge($catchBlock->getMetadata());
        }

        return $metadata;
    }

    private function catchBlockResolvedTemplateMutator(string $resolvedTemplate): string
    {
        return ' ' . $resolvedTemplate;
    }
}
