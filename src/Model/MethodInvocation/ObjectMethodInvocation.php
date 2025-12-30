<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\MethodInvocation;

use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;

class ObjectMethodInvocation extends AbstractMethodInvocationEncapsulator implements MethodInvocationInterface
{
    private const RENDER_TEMPLATE = '{{ object }}->{{ method_invocation }}';

    private ExpressionInterface $object;

    private bool $isErrorSuppressed = false;

    public function __construct(
        ExpressionInterface $object,
        string $methodName,
        ?MethodArgumentsInterface $arguments = null
    ) {
        parent::__construct($methodName, $arguments);
        $this->object = $object;
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
            'object' => $this->object,
            'method_invocation' => $this->invocation,
        ];
    }

    protected function getAdditionalMetadata(): MetadataInterface
    {
        return $this->object->getMetadata();
    }

    public function setIsErrorSuppressed(bool $isErrorSuppressed): static
    {
        $new = clone $this;
        $new->isErrorSuppressed = $isErrorSuppressed;

        return $new;
    }
}
