<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\MethodInvocation;

use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;

abstract class AbstractMethodInvocationEncapsulator implements InvocableInterface
{
    protected FooMethodInvocation $invocation;

    public function __construct(
        string $methodName,
        MethodArgumentsInterface $arguments,
        bool $mightThrow,
    ) {
        $this->invocation = new FooMethodInvocation($methodName, $arguments, $mightThrow);
    }

    public function getMetadata(): MetadataInterface
    {
        $metadata = $this->invocation->getMetadata();

        return $metadata->merge($this->getAdditionalMetadata());
    }

    public function mightThrow(): bool
    {
        return $this->invocation->mightThrow();
    }

    abstract protected function getAdditionalMetadata(): MetadataInterface;
}
