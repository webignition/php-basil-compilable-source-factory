<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\MethodInvocation;

use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;
use webignition\BasilCompilableSourceFactory\Model\StaticObject;

class ObjectMethodInvocation extends AbstractMethodInvocationEncapsulator implements MethodInvocationInterface
{
    private const string RENDER_TEMPLATE_OBJECT_METHOD_CALL = '->';
    private const string RENDER_TEMPLATE_STATIC_OBJECT_METHOD_CALL = '::';
    private const string RENDER_TEMPLATE_OBJECT = '{{ object }}';
    private const string RENDER_TEMPLATE_METHOD = '{{ method_invocation }}';

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
        $errorSuppressionComponent = $this->isErrorSuppressed ? self::ERROR_SUPPRESSION_PREFIX : '';
        $methodCallComponent = $this->object instanceof StaticObject
            ? self::RENDER_TEMPLATE_STATIC_OBJECT_METHOD_CALL
            : self::RENDER_TEMPLATE_OBJECT_METHOD_CALL;

        return sprintf(
            '%s%s%s%s',
            $errorSuppressionComponent,
            self::RENDER_TEMPLATE_OBJECT,
            $methodCallComponent,
            self::RENDER_TEMPLATE_METHOD,
        );
    }

    public function getContext(): array
    {
        return [
            'object' => $this->object,
            'method_invocation' => $this->invocation,
        ];
    }

    public function setIsErrorSuppressed(bool $isErrorSuppressed): static
    {
        $new = clone $this;
        $new->isErrorSuppressed = $isErrorSuppressed;

        return $new;
    }

    protected function getAdditionalMetadata(): MetadataInterface
    {
        return $this->object->getMetadata();
    }
}
