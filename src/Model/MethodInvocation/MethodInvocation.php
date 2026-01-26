<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\MethodInvocation;

use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;

class MethodInvocation implements MethodInvocationInterface
{
    private const RENDER_TEMPLATE = '{{ call }}({{ arguments }})';

    private string $methodName;
    private MethodArgumentsInterface $arguments;

    private bool $isErrorSuppressed = false;

    private bool $mightThrow = false;

    public function __construct(string $methodName, ?MethodArgumentsInterface $arguments = null)
    {
        $this->methodName = $methodName;
        $this->arguments = $arguments ?? new MethodArguments([]);
    }

    public function getCall(): string
    {
        return $this->methodName;
    }

    public function getArguments(): MethodArgumentsInterface
    {
        return $this->arguments;
    }

    public function getMetadata(): MetadataInterface
    {
        return $this->arguments->getMetadata();
    }

    public function getTemplate(): string
    {
        $template = self::RENDER_TEMPLATE;

        if ($this->isErrorSuppressed) {
            $template = self::ERROR_SUPPRESSION_PREFIX . $template;
        }

        return $template;
    }

    public function getContext(): array
    {
        return [
            'call' => $this->getCall(),
            'arguments' => $this->arguments,
        ];
    }

    public function setIsErrorSuppressed(bool $isErrorSuppressed): static
    {
        $new = clone $this;
        $new->isErrorSuppressed = $isErrorSuppressed;

        return $new;
    }

    public function mightThrow(): bool
    {
        return $this->mightThrow;
    }

    public function withMightThrow(bool $mightThrow): self
    {
        $new = clone $this;
        $new->mightThrow = $mightThrow;

        return $new;
    }
}
