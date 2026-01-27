<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\MethodInvocation;

use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\IsNotStaticTrait;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;

readonly class FooMethodInvocation implements MethodInvocationInterface
{
    use IsNotStaticTrait;

    public function __construct(
        private string $methodName,
        private MethodArgumentsInterface $arguments,
        private bool $mightThrow,
        private ?ExpressionInterface $parent = null,
        private bool $isErrorSuppressed = false,
    ) {}

    public function getMetadata(): MetadataInterface
    {
        $metadata = $this->arguments->getMetadata();

        if ($this->parent instanceof ExpressionInterface) {
            $metadata = $metadata->merge($this->parent->getMetadata());
        }

        return $metadata;
    }

    public function getTemplate(): string
    {
        $template = '{{ methodName }}({{ arguments }})';

        if ($this->parent instanceof ExpressionInterface) {
            $template = '{{ parent }}{{ accessor }}' . $template;
        }

        if ($this->isErrorSuppressed) {
            $template = '@' . $template;
        }

        return $template;
    }

    public function getContext(): array
    {
        $context = [
            'methodName' => $this->methodName,
            'arguments' => $this->arguments,
        ];

        if ($this->parent instanceof ExpressionInterface) {
            $context['parent'] = $this->parent;
            $context['accessor'] = $this->parent->isStatic() ? '::' : '->';
        }

        return $context;
    }

    public function setIsErrorSuppressed(): self
    {
        return new FooMethodInvocation(
            $this->methodName,
            $this->arguments,
            $this->mightThrow,
            $this->parent,
            true,
        );
    }

    public function mightThrow(): bool
    {
        return $this->mightThrow;
    }
}
