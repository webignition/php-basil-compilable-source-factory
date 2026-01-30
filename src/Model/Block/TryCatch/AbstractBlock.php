<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Block\TryCatch;

use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\HasMetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\IndentTrait;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MightThrowInterface;
use webignition\Stubble\Resolvable\ResolvableInterface;
use webignition\Stubble\Resolvable\ResolvedTemplateMutatorResolvable;

abstract class AbstractBlock implements HasMetadataInterface, ResolvableInterface, MightThrowInterface
{
    use IndentTrait;

    protected BodyInterface $body;

    public function __construct(BodyInterface $body)
    {
        $this->body = $body;
    }

    public function getMetadata(): MetadataInterface
    {
        return $this->body->getMetadata();
    }

    public function mightThrow(): bool
    {
        return $this->body->mightThrow();
    }

    public function getBody(): BodyInterface
    {
        return $this->body;
    }

    protected function createResolvableBody(): ResolvableInterface
    {
        return new ResolvedTemplateMutatorResolvable(
            $this->body,
            function (string $resolvedTemplate): string {
                return rtrim($this->indent($resolvedTemplate));
            }
        );
    }
}
