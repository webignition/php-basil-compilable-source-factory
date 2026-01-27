<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\MethodInvocation;

use webignition\BasilCompilableSourceFactory\Model\IsNotStaticTrait;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;

class MethodInvocation implements MethodInvocationInterface
{
    use IsNotStaticTrait;

    private const string RENDER_TEMPLATE = '{{ call }}({{ arguments }})';

    private bool $isErrorSuppressed = false;

    public function __construct(
        private string $methodName,
        private MethodArgumentsInterface $arguments,
        private bool $mightThrow
    ) {}

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
            'call' => $this->methodName,
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
}
