<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\MethodInvocation;

use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;

abstract class AbstractMethodInvocationEncapsulator implements InvocableInterface
{
    protected MethodInvocation $invocation;

    public function __construct(
        string $methodName,
        MethodArgumentsInterface $arguments,
        bool $mightThrow,
    ) {
        $this->invocation = new MethodInvocation($methodName, $arguments, $mightThrow);
    }

    public function getMetadata(): MetadataInterface
    {
        $metadata = $this->invocation->getMetadata();

        return $metadata->merge($this->getAdditionalMetadata());
    }

    public function getCall(): string
    {
        return $this->invocation->getCall();
    }

    public function getArguments(): MethodArgumentsInterface
    {
        return $this->invocation->getArguments();
    }

    abstract protected function getAdditionalMetadata(): MetadataInterface;

    public function mightThrow(): bool
    {
        return $this->invocation->mightThrow();
    }
}
