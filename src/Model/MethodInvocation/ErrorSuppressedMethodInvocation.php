<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\MethodInvocation;

use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;

class ErrorSuppressedMethodInvocation implements MethodInvocationInterface
{
    private MethodInvocationInterface $invocation;

    public function __construct(MethodInvocationInterface $invocation)
    {
        $this->invocation = $invocation;
    }

    public function getMetadata(): MetadataInterface
    {
        return $this->invocation->getMetadata();
    }

    public function getCall(): string
    {
        return $this->invocation->getCall();
    }

    public function getArguments(): MethodArgumentsInterface
    {
        return $this->invocation->getArguments();
    }

    public function getTemplate(): string
    {
        return '@' . $this->invocation->getTemplate();
    }

    public function getContext(): array
    {
        return $this->invocation->getContext();
    }

    public function setIsErrorSuppressed(bool $isErrorSuppressed): static
    {
        return $this;
    }
}
